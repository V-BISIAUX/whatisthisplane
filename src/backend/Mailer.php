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
	public function sendVerificationEmail(string $to, string $username, string $token, int $expirationSeconds): bool {
		$verificationUrl = URL . '/ajax/user/verify_email.php';
		$expiration = $this->formatTime($expirationSeconds);
		
		$subject = "Vérification de votre adresse e-mail";
		$body = sprintf(
			'<p>Bonjour %s,</p>
			 <p>Merci pour votre inscription. Pour valider votre compte, veuillez cliquer sur le lien ci-dessous :</p>
			 <p><a href="%s">Valider mon adresse e-mail</a></p>
			 <p>Ce lien expirera dans %s.</p>',
			htmlspecialchars($username),
			htmlspecialchars($verificationUrl),
			htmlspecialchars($expiration)
		);

		return $this->send($to, $subject, $body);
	}
	
    /**
	 * Envoie un email de réinitialisation de mot de passe
	 */
	public function sendPasswordResetEmail(string $to, string $username, string $resetToken, int $expirationSeconds): bool {
		$resetUrl = URL . '/ajax/user/reset_password.php';
		$expiration = $this->formatTime($expirationSeconds);
		
		$subject = "Réinitialisation de votre mot de passe";
		$body = sprintf(
			'<p>Bonjour %s,</p>
			 <p>Pour réinitialiser votre mot de passe, cliquez sur le lien ci-dessous :</p>
			 <p><a href="%s">Réinitialiser mon mot de passe</a></p>
			 <p>Ce lien expirera dans %s.</p>',
			htmlspecialchars($username),
			htmlspecialchars($resetUrl),
			htmlspecialchars($expiration)
		);
		
		return $this->send($to, $subject, $body);
	}
	
	/**
	 * Convertie le temps, donné en seconde, en array contenant les jours, heures, minutes et secondes
	 */
	private function convertTime(int $seconds) : string {
	    $seconds = max(0, $seconds);
	    
	    $days = intdiv($seconds, 86400);
	    $seconds %= 86400;
	    
	    $hours = intdiv($seconds, 3600);
	    $seconds %= 3600;
	    
	    $minutes = intdiv($seconds, 60);
	    $seconds %= 60;
	    
	    $parts = [];

		if ($days > 0) {
			$parts[] = $days . ' jour' . ($days > 1 ? 's' : '');
		}if ($hours > 0) {
			$parts[] = $hours . ' heure' . ($hours > 1 ? 's' : '');
		}if ($minutes > 0) {
			$parts[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
		}if ($seconds > 0) {
			$parts[] = $seconds . ' seconde' . ($seconds > 1 ? 's' : '');
		}
		if (empty($parts)) {
			return '0 seconde';
		}

		if (count($parts) === 1) {
			return $parts[0];
		} elseif (count($parts) === 2) {
			return implode(' et ', $parts);
		} else {
			$last = array_pop($parts);
			return implode(', ', $parts) . ' et ' . $last;
		}
	}
}
