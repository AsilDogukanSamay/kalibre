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
    /** Semadaki ENUM ile birebir. Disaridan gelen deger bu listeye karsi dogrulanir. */
    public const STATUSES = ['new', 'read', 'archived'];

    public function __construct(private ?PDO $pdo = null)
    {
        $this->pdo ??= Database::connection();
    }

    /**
     * @param array{full_name:string,email:string,phone:string,message:string} $data
     * @param string $consentVersion Onaylanan aydinlatma metninin surumu (KVKK ispat kaydi)
     * @return int Eklenen kaydin birincil anahtari
     */
    public function create(array $data, string $ip, string $userAgent, string $consentVersion = ''): int
    {
        $sql = 'INSERT INTO contact_messages
                    (full_name, email, phone, message, ip_address, user_agent,
                     consent_at, consent_version, created_at)
                VALUES
                    (:full_name, :email, :phone, :message, :ip_address, :user_agent,
                     NOW(), :consent_version, NOW())';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':full_name'       => $data['full_name'],
            ':email'           => $data['email'],
            ':phone'           => $data['phone'],
            ':message'         => $data['message'],
            ':ip_address'      => $ip,
            ':user_agent'      => $userAgent,
            ':consent_version' => $consentVersion,
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

    // ----------------------------------------------------------------
    // Yonetim paneli sorgulari
    // ----------------------------------------------------------------

    /**
     * Talep listesi. $status verilirse yalnizca o durum doner.
     * LIMIT/OFFSET tam sayi olarak baglanir, metne birlestirilmez.
     *
     * @return array<int,array<string,mixed>>
     */
    public function page(?string $status, int $limit, int $offset): array
    {
        $status = $this->normalizeStatus($status);

        $sql = 'SELECT id, full_name, email, phone, message, ip_address,
                       status, consent_at, consent_version, created_at
                  FROM contact_messages'
             . ($status !== null ? ' WHERE status = :status' : '')
             . ' ORDER BY created_at DESC, id DESC
                 LIMIT :limit OFFSET :offset';

        $stmt = $this->pdo->prepare($sql);
        if ($status !== null) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByStatus(?string $status): int
    {
        $status = $this->normalizeStatus($status);

        $sql = 'SELECT COUNT(*) FROM contact_messages'
             . ($status !== null ? ' WHERE status = :status' : '');

        $stmt = $this->pdo->prepare($sql);
        if ($status !== null) {
            $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Durum basina sayac. Panelde sekme rozetleri icin tek sorguda alinir.
     *
     * @return array<string,int>
     */
    public function statusCounts(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT status, COUNT(*) AS adet FROM contact_messages GROUP BY status'
        );
        $stmt->execute();

        $counts = array_fill_keys(self::STATUSES, 0);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $counts[(string) $row['status']] = (int) $row['adet'];
        }

        return $counts;
    }

    public function updateStatus(int $id, string $status): bool
    {
        if (!in_array($status, self::STATUSES, true)) {
            return false;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE contact_messages SET status = :status WHERE id = :id'
        );
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /** Sema disindaki bir durum degeri sorguya hic girmez. */
    private function normalizeStatus(?string $status): ?string
    {
        return $status !== null && in_array($status, self::STATUSES, true) ? $status : null;
    }
}
