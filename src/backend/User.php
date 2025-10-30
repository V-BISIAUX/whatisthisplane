<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/Mailer.php';

/**
 * Classe User - Gestion des utilisateurs
 * 
 * Responsabilités :
 * - Inscription et validation email
 * - Connexion et authentification
 * - Gestion du profil utilisateur
 * - Modification mot de passe
 * - Suppression de compte
 */
class User {
    
    private const MIN_USERNAME_LENGTH = 1;
    private const MAX_USERNAME_LENGTH = 50;
    private const MIN_EMAIL_LENGTH = 5;
    private const MAX_EMAIL_LENGTH = 255;
    private const MIN_PASSWORD_LENGTH = 8;
    private const MAX_PASSWORD_LENGTH = 64;
    private const TOKEN_LENGTH = 64;
    private const DEFAULT_TOKEN_EXPIRATION = 600; // 10 minutes
    private const MAX_TOKEN_EXPIRATION = 3600; // 1 heure
    
    private mysqli $mysqli;
    
    /**
     * Constructeur - Initialise la connexion à la base de données
     * @throws Exception Si la connexion échoue
     */
    public function __construct() {
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($this->mysqli->connect_error) {
            throw new Exception('Erreur de connexion à la base de données : ' . $this->mysqli->connect_error);
        }
        
        // Définir le charset
        $this->mysqli->set_charset('utf8mb4');
    }
    
    /**
     * Destructeur - Ferme la connexion à la base de données
     */
    public function __destruct() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
    
    // ============================================
    // MÉTHODES PUBLIQUES - PRINCIPALES
    // ============================================
    
    /**
     * Inscrit un nouvel utilisateur
     * 
     * @param string $username Nom d'utilisateur
     * @param string $email Email
     * @param string $password Mot de passe en clair
     * @return array ['success' => bool, 'message'|'error' => string]
     */
    public function register(string $username, string $email, string $password): array {
        // Nettoyage des entrées
        $username = trim($username);
        $email = trim($email);
        $password = trim($password);
        
        // Validations
        $validationResult = $this->validateRegistrationData($username, $email, $password);
        if (!$validationResult['success']) {
            return $validationResult;
        }
        
        // Vérifier doublon email
        if ($this->emailExists($email)) {
            return ['success' => false, 'error' => "Cet email est déjà enregistré"];
        }
		
		// Vérifier doublon username
		if ($this->usernameExists($username)) {
            return ['success' => false, 'error' => "Ce nom d'utilisateur est déjà pris"];
        }
		
		// Vérification du domaine MX
		$domain = substr(strrchr($email, "@"), 1);
		if (!checkdnsrr($domain, 'MX')) {
			return ['success' => false, 'error' => "Le domaine de l'email ne semble pas recevoir des emails."];
		}
        
        // Préparer les données
        $hashedPassword = $this->hashPassword($password);
        $token = $this->generateToken();
        $tokenExpiration = $this->generateExpirationToken(self::DEFAULT_TOKEN_EXPIRATION);
        
        // Insertion en BDD
        if (!$this->insertUser($username, $email, $hashedPassword, $token, $tokenExpiration)) {
			return ['success' => false, 'error' => "Erreur lors de l'inscription"];
		}
		
		// Envoi du mail de vérification via Mailer
		try {
			$mailer = new Mailer();
			$expirationMinutes = (int)(self::DEFAULT_TOKEN_EXPIRATION / 60);
			
			if (!$mailer->sendVerificationEmail($email, $username, $token, $expirationMinutes)) {
				return ['success' => false, 'error' => "Impossible d'envoyer l'email de validation"];
			}
		} catch (Exception $e) {
			return ['success' => false, 'error' => "Erreur lors de l'envoi de l'email : " . $e->getMessage()];
		}

		return ['success' => true, 'message' => "Compte créé. Vérifiez vos emails"];
	}
    
    /**
     * Connecte un utilisateur
     * 
     * @param string $identifier Email ou nom d'utilisateur
     * @param string $password Mot de passe en clair
     * @return array ['success' => bool, 'user'|'error' => mixed]
     */
    public function login(string $identifier, string $password): array {
        $identifier = trim($identifier);
        $password = trim($password);
        
        // Récupérer l'utilisateur
        $user = $this->getUserByEmailOrUsername($identifier);
        
        if (!$user) {
            return ['success' => false, 'error' => "Identifiant ou mot de passe incorrects"];
        }
        
        // Vérifier le mot de passe
        if (!$this->verifyPassword($password, $user['password'])) {
            return ['success' => false, 'error' => "Identifiant ou mot de passe incorrects"];
        }
        
        // Vérifier la validation email
        if (!$user['email_verified']) {
            return ['success' => false, 'error' => "Veuillez valider votre email avant de vous connecter"];
        }
        
        return [
            'success' => true,
            'user' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ];
    }
    
    /**
     * Valide l'email d'un utilisateur via token
     * 
     * @param string $token Token de vérification
     * @return array ['success' => bool, 'message'|'error' => string]
     */
    public function verifyEmail(string $token): array {
        $token = trim($token);
        
        if (empty($token)) {
            return ['success' => false, 'error' => "Token invalide"];
        }
        
        // Récupérer les données utilisateur
        $userData = $this->getUserDataByToken($token);
        
        if (!$userData) {
            return ['success' => false, 'error' => "Token invalide ou déjà utilisé"];
        }
        
        // Vérifier expiration
        if (strtotime($userData['token_expiration']) < time()) {
            return ['success' => false, 'error' => "Ce lien de validation a expiré"];
        }
        
        // Marquer comme vérifié
        if (!$this->markEmailVerified((int)$userData['user_id'])) {
            return ['success' => false, 'error' => "Erreur lors de la validation de l'email"];
        }
        
        return ['success' => true, 'message' => "Email vérifié ! Vous pouvez vous connecter"];
    }
    
    /**
     * Récupère le profil d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @return array ['success' => bool, 'user'|'error' => mixed]
     */
    public function getProfile(int $userId): array {
        $user = $this->getUserById($userId);
        
        if (!$user) {
            return ['success' => false, 'error' => "Utilisateur introuvable"];
        }
        
        $nbFavorites = $this->countUserFavorites($userId);
        
        return [
            'success' => true,
            'user' => [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'created_at' => $user['created_at'],
                'nb_favorites' => $nbFavorites
            ]
        ];
    }
    
    /**
     * Change le mot de passe d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $oldPassword Ancien mot de passe
     * @param string $newPassword Nouveau mot de passe
     * @return array ['success' => bool, 'message'|'error' => string]
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): array {
        // Récupérer le hash actuel
        $currentHash = $this->getUserPasswordHash($userId);
        
        if (!$currentHash) {
            return ['success' => false, 'error' => "Utilisateur introuvable"];
        }
        
        // Vérifier l'ancien mot de passe
        if (!$this->verifyPassword($oldPassword, $currentHash)) {
            return ['success' => false, 'error' => "Mot de passe actuel incorrect"];
        }
        
        // Valider le nouveau mot de passe
        $validation = $this->validatePassword($newPassword);
        if (!$validation['success']) {
            return $validation;
        }
        
        // Hasher et mettre à jour
        $newHash = $this->hashPassword($newPassword);
        
        if (!$this->updatePassword($userId, $newHash)) {
            return ['success' => false, 'error' => "Erreur lors de la mise à jour du mot de passe"];
        }
        
        return ['success' => true, 'message' => "Mot de passe modifié"];
    }
	
	/**
	 * Demande une réinitialisation de mot de passe (envoie email avec token)
	 * 
	 * @param string $email Email de l'utilisateur
	 * @return array ['success' => bool, 'message'|'error' => string]
	 */
	public function requestPasswordReset(string $email): array {
		$email = trim($email);
		
		// Récupérer l'utilisateur par email
		$user = $this->getUserByEmailOrUsername($email);
		
		if (!$user) {
			return ['success' => true, 'message' => "Si cet email existe, un lien de réinitialisation a été envoyé"];
		}
		
		// Vérifier que le compte est vérifié
		if (!$user['email_verified']) {
			return ['success' => false, 'error' => "Veuillez d'abord valider votre email"];
		}
		
		// Générer un nouveau token
		$token = $this->generateToken();
		$tokenExpiration = $this->generateExpirationToken(self::DEFAULT_TOKEN_EXPIRATION);
		
		// Mettre à jour le token en BDD
		if (!$this->updateResetToken((int)$user['user_id'], $token, $tokenExpiration)) {
			return ['success' => false, 'error' => "Erreur lors de la génération du lien"];
		}
		
		// Envoyer l'email
		try {
			$mailer = new Mailer();
			$expirationMinutes = (int)(self::DEFAULT_TOKEN_EXPIRATION / 60);
			
			if (!$mailer->sendPasswordResetEmail($email, $user['username'], $token, $expirationMinutes)) {
				return ['success' => false, 'error' => "Impossible d'envoyer l'email"];
			}
		} catch (Exception $e) {
			return ['success' => false, 'error' => "Erreur lors de l'envoi de l'email : " . $e->getMessage()];
		}
		
		return ['success' => true, 'message' => "Un email de réinitialisation a été envoyé"];
	}
	
	/**
	 * Réinitialise le mot de passe via token (sans ancien mot de passe)
	 * 
	 * @param string $token Token de réinitialisation
	 * @param string $newPassword Nouveau mot de passe
	 * @return array ['success' => bool, 'message'|'error' => string]
	 */
	public function resetPassword(string $token, string $newPassword): array {
		$token = trim($token);
		$newPassword = trim($newPassword);
		
		if (empty($token)) {
			return ['success' => false, 'error' => "Token invalide"];
		}
		
		// Valider le nouveau mot de passe
		$validation = $this->validatePassword($newPassword);
		if (!$validation['success']) {
			return $validation;
		}
		
		// Récupérer les données utilisateur par token
		$userData = $this->getUserDataByToken($token);
		
		if (!$userData) {
			return ['success' => false, 'error' => "Token invalide ou déjà utilisé"];
		}
		
		// Vérifier l'expiration
		if (strtotime($userData['token_expiration']) < time()) {
			return ['success' => false, 'error' => "Ce lien de réinitialisation a expiré"];
		}
		
		// Hasher le nouveau mot de passe
		$newHash = $this->hashPassword($newPassword);
		
		// Mettre à jour le mot de passe ET supprimer le token
		if (!$this->updatePasswordAndClearToken((int)$userData['user_id'], $newHash)) {
			return ['success' => false, 'error' => "Erreur lors de la réinitialisation"];
		}
		
		return ['success' => true, 'message' => "Mot de passe réinitialisé avec succès"];
	}
    
    /**
     * Supprime le compte d'un utilisateur
     * 
     * @param int $userId ID de l'utilisateur
     * @param string $password Mot de passe de confirmation
     * @return array ['success' => bool, 'message'|'error' => string]
     */
    public function deleteAccount(int $userId, string $password): array {
        // Vérifier le mot de passe
        $hash = $this->getUserPasswordHash($userId);
        
        if (!$hash) {
            return ['success' => false, 'error' => "Utilisateur introuvable"];
        }
        
        if (!$this->verifyPassword($password, $hash)) {
            return ['success' => false, 'error' => "Mot de passe incorrect"];
        }
        
        // Supprimer (CASCADE supprimera automatiquement les favoris)
        if (!$this->deleteUser($userId)) {
            return ['success' => false, 'error' => "Erreur lors de la suppression du compte"];
        }
        
        return ['success' => true, 'message' => "Compte supprimé"];
    }
    
    // ============================================
    // MÉTHODES PRIVÉES - VALIDATION
    // ============================================
    
    /**
     * Valide toutes les données d'inscription
     */
    private function validateRegistrationData(string $username, string $email, string $password): array {
        // Validation username
        $usernameCheck = $this->validateUsername($username);
        if (!$usernameCheck['success']) {
            return $usernameCheck;
        }
        
        // Validation email
        $emailCheck = $this->validateEmail($email);
        if (!$emailCheck['success']) {
            return $emailCheck;
        }
        
        // Validation password
        $passwordCheck = $this->validatePassword($password);
        if (!$passwordCheck['success']) {
            return $passwordCheck;
        }
        
        return ['success' => true];
    }
    
    /**
     * Valide un nom d'utilisateur
     */
    private function validateUsername(string $username): array {
        $length = strlen($username);
        
        if ($length < self::MIN_USERNAME_LENGTH) {
            return ['success' => false, 'error' => "Le nom d'utilisateur doit contenir au moins " . self::MIN_USERNAME_LENGTH . " caractère"];
        }
        
        if ($length > self::MAX_USERNAME_LENGTH) {
            return ['success' => false, 'error' => "Le nom d'utilisateur est trop long (max " . self::MAX_USERNAME_LENGTH . " caractères)"];
        }
        
        return ['success' => true];
    }
    
    /**
     * Valide un email
     */
    private function validateEmail(string $email): array {
        $length = strlen($email);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => "Format email invalide"];
        }
        
        if ($length < self::MIN_EMAIL_LENGTH) { 
            return ['success' => false, 'error' => "L'email est trop court"];
        }
        
        if ($length > self::MAX_EMAIL_LENGTH) {
            return ['success' => false, 'error' => "L'email est trop long (max " . self::MAX_EMAIL_LENGTH . " caractères)"];
        }
        
        return ['success' => true];
    }
    
    /**
     * Valide un mot de passe
     */
    private function validatePassword(string $password): array {
        $length = strlen($password);
        
        if ($length < self::MIN_PASSWORD_LENGTH) {
            return ['success' => false, 'error' => "Le mot de passe doit contenir au moins " . self::MIN_PASSWORD_LENGTH . " caractères"];
        }
        
        if ($length > self::MAX_PASSWORD_LENGTH) {
            return ['success' => false, 'error' => "Le mot de passe est trop long (max " . self::MAX_PASSWORD_LENGTH . " caractères)"];
        }
        
        return ['success' => true];
    }
    
    // ============================================
    // MÉTHODES PRIVÉES - BASE DE DONNÉES
    // ============================================
    
    /**
     * Vérifie si un email existe déjà
     */
    private function emailExists(string $email): bool {
        $stmt = $this->mysqli->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }
	
	    
    /**
     * Vérifie si un email existe déjà
     */
    private function usernameExists(string $username): bool {
        $stmt = $this->mysqli->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }
    
    /**
     * Insère un nouvel utilisateur en BDD
     */
    private function insertUser(string $username, string $email, string $hashedPassword, string $token, string $tokenExpiration): bool {
        $stmt = $this->mysqli->prepare(
            "INSERT INTO users (username, email, password, email_verified, verification_token, token_expiration) 
             VALUES (?, ?, ?, 0, ?, ?)"
        );
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('sssss', $username, $email, $hashedPassword, $token, $tokenExpiration);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Récupère un utilisateur par email ou username
     */
    private function getUserByEmailOrUsername(string $identifier): ?array {
        $stmt = $this->mysqli->prepare(
            "SELECT user_id, username, email, password, email_verified 
             FROM users 
             WHERE email = ? OR username = ? 
             LIMIT 1"
        );
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('ss', $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user ?: null;
    }
    
    /**
     * Récupère les données utilisateur par token
     */
    private function getUserDataByToken(string $token): ?array {
        $stmt = $this->mysqli->prepare(
            "SELECT user_id, token_expiration 
             FROM users 
             WHERE verification_token = ? 
             LIMIT 1"
        );
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $stmt->close();
        
        return $userData ?: null;
    }
    
    /**
     * Marque l'email comme vérifié
     */
    private function markEmailVerified(int $userId): bool {
        $stmt = $this->mysqli->prepare(
            "UPDATE users 
             SET email_verified = 1, verification_token = NULL, token_expiration = NULL 
             WHERE user_id = ?"
        );
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('i', $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
	
	/**
	 * Met à jour le token de réinitialisation
	 */
	private function updateResetToken(int $userId, string $token, string $tokenExpiration): bool {
		$stmt = $this->mysqli->prepare(
			"UPDATE users 
			 SET verification_token = ?, token_expiration = ? 
			 WHERE user_id = ? AND email_verified = 1"
		);
		
		if (!$stmt) {
			throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
		}
		
		$stmt->bind_param('ssi', $token, $tokenExpiration, $userId);
		$success = $stmt->execute();
		$stmt->close();
		
		return $success;
	}
	
	/**
	 * Met à jour le mot de passe et supprime le token
	 */
	private function updatePasswordAndClearToken(int $userId, string $newHash): bool {
		$stmt = $this->mysqli->prepare(
			"UPDATE users 
			 SET password = ?, verification_token = NULL, token_expiration = NULL, updated_at = NOW() 
			 WHERE user_id = ?"
		);
		
		if (!$stmt) {
			throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
		}
		
		$stmt->bind_param('si', $newHash, $userId);
		$success = $stmt->execute();
		$stmt->close();
		
		return $success;
	}
    
    /**
     * Récupère un utilisateur par ID
     */
    private function getUserById(int $userId): ?array {
        $stmt = $this->mysqli->prepare(
            "SELECT user_id, username, email, created_at 
             FROM users 
             WHERE user_id = ? 
             LIMIT 1"
        );
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user ?: null;
    }
    
    /**
     * Compte le nombre de favoris d'un utilisateur
     */
    private function countUserFavorites(int $userId): int {
        $stmt = $this->mysqli->prepare(
            "SELECT COUNT(*) as nb_favorites 
             FROM favorites 
             WHERE user_id = ?"
        );
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count ?? 0;
    }
    
    /**
     * Récupère le hash du mot de passe d'un utilisateur
     */
    private function getUserPasswordHash(int $userId): ?string {
        $stmt = $this->mysqli->prepare(
            "SELECT password 
             FROM users 
             WHERE user_id = ? 
             LIMIT 1"
        );
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();
        $stmt->close();
        
        return $hash ?: null;
    }
    
    /**
     * Met à jour le mot de passe en BDD
     */
    private function updatePassword(int $userId, string $newHash): bool {
        $stmt = $this->mysqli->prepare(
            "UPDATE users 
             SET password = ?, updated_at = NOW() 
             WHERE user_id = ?"
        );
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('si', $newHash, $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Supprime un utilisateur de la BDD (CASCADE supprime les favoris)
     */
    private function deleteUser(int $userId): bool {
        $stmt = $this->mysqli->prepare("DELETE FROM users WHERE user_id = ?");
        
        if (!$stmt) {
            throw new Exception('Erreur préparation requête : ' . $this->mysqli->error);
        }
        
        $stmt->bind_param('i', $userId);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    // ============================================
    // MÉTHODES PRIVÉES - UTILITAIRES
    // ============================================
    
    /**
     * Vérifie un mot de passe contre son hash
     */
    private function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * Hash un mot de passe
     */
    private function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Génère un token aléatoire
     */
    private function generateToken(int $length = self::TOKEN_LENGTH): string {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Génère une date d'expiration pour le token
     */
    private function generateExpirationToken(int $duration = self::DEFAULT_TOKEN_EXPIRATION): string {
        $duration = min(self::MAX_TOKEN_EXPIRATION, $duration);
        return date('Y-m-d H:i:s', time() + $duration);
    }
}