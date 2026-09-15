<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * contact_messages tablosunun veri erisim katmani.
 * Tum sorgular PDO prepared statement ile calisir; kullanici girdisi
 * hicbir zaman SQL metnine birlestirilmez.
 */
final class ContactMessage
{
    public function __construct(private ?PDO $pdo = null)
    {
        $this->pdo ??= Database::connection();
    }

    /**
     * @param array{full_name:string,email:string,phone:string,message:string} $data
     * @return int Eklenen kaydin birincil anahtari
     */
    public function create(array $data, string $ip, string $userAgent): int
    {
        $sql = 'INSERT INTO contact_messages
                    (full_name, email, phone, message, ip_address, user_agent, created_at)
                VALUES
                    (:full_name, :email, :phone, :message, :ip_address, :user_agent, NOW())';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':full_name'  => $data['full_name'],
            ':email'      => $data['email'],
            ':phone'      => $data['phone'],
            ':message'    => $data['message'],
            ':ip_address' => $ip,
            ':user_agent' => $userAgent,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Ayni IP'nin son $minutes dakikadaki gonderim sayisi.
     * Basit spam frenidir, uygulama katmaninda esik ile karsilastirilir.
     */
    public function recentCountByIp(string $ip, int $minutes): int
    {
        $sql = 'SELECT COUNT(*) FROM contact_messages
                WHERE ip_address = :ip
                  AND created_at >= (NOW() - INTERVAL :minutes MINUTE)';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ip', $ip, PDO::PARAM_STR);
        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}
