<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

class Mailer {
    private PHPMailer $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);

        try {
            $this->mailer->isSMTP();
            $this->mailer->Host = MAIL_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = MAIL_USER;
            $this->mailer->Password = MAIL_PASS;
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = MAIL_PORT;
            $this->mailer->setFrom(MAIL_FROM, 'WhatIsThisPlane');
            $this->mailer->isHTML(true);
            $this->mailer->CharSet = 'UTF-8';
        } catch (Exception $e) {
            throw new Exception('Erreur Mailer: ' . $e->getMessage());
        }
    }

    /**
     * Envoie un email générique avec un corps HTML personnalisé
     */
    public function send(string $to, string $subject, string $htmlBody): bool {
        try {
            $this->mailer->clearAllRecipients();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $htmlBody;
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log('Erreur envoi mail: ' . $e->getMessage());
            return false;
        }
    }

	/**
	 * Envoie l'email de vérification après inscription
	 */
	public function sendVerificationEmail(string $to, string $username, string $token, int $expirationSecond): bool {
		string $verificationUrl = URL . '/ajax/user/verify_email.php';
		array $expiration = convertTime
		
		string $subject = "Validation de votre inscription";
		string $body = "
			<p>Bonjour <strong>" . htmlspecialchars($username) . "</strong>,</p>
			<p>Merci de vous être inscrit. Pour valider votre compte, veuillez cliquer sur le lien suivant :</p>
			<p><a href=\"" . htmlspecialchars($verificationUrl) . "?token=" . urlencode($token) . "\">Valider mon email</a></p>
			<p><strong>⏱️ Ce lien expirera dans {$expiration}.</strong></p>
			<p>Si vous n'avez pas demandé cette inscription, ignorez cet email.</p>
		";

		return $this->send($to, $subject, $body);
	}
	
    /**
	 * Envoie un email de réinitialisation de mot de passe
	 */
	public function sendPasswordResetEmail(string $to, string $username, string $resetToken, int $expirationSecond): bool {
		string $resetUrl = URL . '/ajax/user/reset_password.php';
		
		string $subject = "Réinitialisation de votre mot de passe";
		string $body = "
			<p>Bonjour <strong>" . htmlspecialchars($username) . "</strong>,</p>
			<p>Vous avez demandé une réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous :</p>
			<p><a href=\"" . htmlspecialchars($resetUrl) . "?token=" . urlencode($resetToken) . "\">Réinitialiser mon mot de passe</a></p>
			<p><strong>⏱️ Ce lien expirera dans {$expiration}.</strong></p>
			<p>Si vous n'avez pas demandé cette modification, ignorez cet email.</p>
		";
		
		return $this->send($to, $subject, $body);
	}
	
	/**
	 * Convertie le temps, donné en seconde, en array contenant les jours, heures, minutes et secondes
	 */
	private function convertTime(int $seconds) : string {
	    $seconds = max(0, $seconds);
	    
	    int $days = intdiv($seconds, 86400);
	    $seconds %= 86400;
	    
	    int $hours = intdiv($seconds, 3600);
	    $seconds %= 3600;
	    
	    int $minutes = intdiv($seconds, 60);
	    $seconds %= 60;
	    
	    string $time = '';
	    
	    return ;
	}
}

