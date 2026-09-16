<?php
/**
 * Talep listesi.
 *
 * @var array<int,array<string,mixed>> $messages
 * @var array<string,int> $counts
 * @var array<string,string> $labels
 * @var string|null $filter
 * @var int $page
 * @var int $pageCount
 * @var int $total
 * @var string $csrf
 * @var string|null $flash
 * @var string $backTo
 */
$tabs = ['' => 'Tümü'] + $labels;
?>
<div class="admin-shell">

    <div class="admin-head">
        <div class="flex flex-col gap-2">
            <p class="eyebrow-label">Randevu talepleri</p>
            <h1 class="h-card"><?= e((string) $total) ?> kayıt<?= $filter !== null ? ' · ' . e($labels[$filter]) : '' ?></h1>
        </div>

        <nav class="admin-tabs" aria-label="Duruma göre süz">
            <?php foreach ($tabs as $key => $label): ?>
                <?php
                $active = ($key === '' && $filter === null) || $key === $filter;
                $adet   = $key === '' ? array_sum($counts) : ($counts[$key] ?? 0);
                $href   = $key === '' ? '/yonetim' : '/yonetim?durum=' . rawurlencode((string) $key);
                ?>
                <a class="admin-tab<?= $active ? ' admin-tab-on' : '' ?>"
                   href="<?= e($href) ?>"
                   <?= $active ? 'aria-current="page"' : '' ?>>
                    <?= e($label) ?>
                    <span class="admin-count"><?= e((string) $adet) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <?php if ($flash !== null): ?>
        <p class="glass-soft mb-6 border-good/40 bg-good/[0.10] p-3 text-sm text-ink" role="status">
            <?= e($flash) ?>
        </p>
    <?php endif; ?>

    <?php if ($messages === []): ?>
        <p class="admin-empty">Bu süzgeçte kayıt yok.</p>
    <?php else: ?>
        <div class="admin-list">
            <?php foreach ($messages as $message): ?>
                <?php $status = (string) $message['status']; ?>
                <article class="admin-card">

                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="flex flex-col gap-1">
                            <h2 class="admin-name"><?= e((string) $message['full_name']) ?></h2>
                            <p class="admin-contact">
                                <a class="admin-link" href="tel:<?= e(str_replace(' ', '', (string) $message['phone'])) ?>"><?= e((string) $message['phone']) ?></a>
                                <a class="admin-link" href="mailto:<?= e((string) $message['email']) ?>"><?= e((string) $message['email']) ?></a>
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-1.5">
                            <span class="status-pill status-<?= e($status) ?>"><?= e($labels[$status] ?? $status) ?></span>
                            <span class="text-xs text-ink-faint"><?= e(sprintf('KLB-%06d', (int) $message['id'])) ?></span>
                        </div>
                    </div>

                    <p class="admin-msg"><?= e((string) $message['message']) ?></p>

                    <p class="admin-meta">
                        <span><?= e(date('d.m.Y H:i', strtotime((string) $message['created_at']))) ?></span>
                        <span>IP <?= e((string) $message['ip_address']) ?></span>
                        <?php if (!empty($message['consent_at'])): ?>
                            <span>
                                KVKK onayı <?= e(date('d.m.Y H:i', strtotime((string) $message['consent_at']))) ?>
                                <?php if (($message['consent_version'] ?? '') !== ''): ?>
                                    · metin <?= e((string) $message['consent_version']) ?>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <span class="text-warn">KVKK onayı kaydı yok</span>
                        <?php endif; ?>
                    </p>

                    <div class="admin-actions">
                        <?php foreach ($labels as $key => $label): ?>
                            <?php if ($key === $status) { continue; } ?>
                            <form method="post" action="/yonetim/durum">
                                <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
                                <input type="hidden" name="id" value="<?= e((string) $message['id']) ?>">
                                <input type="hidden" name="durum" value="<?= e((string) $key) ?>">
                                <input type="hidden" name="geri" value="<?= e($backTo) ?>">
                                <button class="admin-chip" type="submit"><?= e($label) ?> olarak işaretle</button>
                            </form>
                        <?php endforeach; ?>
                    </div>

                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($pageCount > 1): ?>
            <nav class="admin-pager" aria-label="Sayfalar">
                <?php
                $query = static fn (int $hedef): string => '/yonetim?'
                    . http_build_query(array_filter([
                        'durum' => $filter,
                        'sayfa' => $hedef > 1 ? $hedef : null,
                    ]));
                ?>
                <?php if ($page > 1): ?>
                    <a class="btn-ghost btn-sm" href="<?= e($query($page - 1)) ?>">Önceki</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>

                <span class="text-sm text-ink-muted"><?= e((string) $page) ?> / <?= e((string) $pageCount) ?></span>

                <?php if ($page < $pageCount): ?>
                    <a class="btn-ghost btn-sm" href="<?= e($query($page + 1)) ?>">Sonraki</a>
                <?php else: ?>
                    <span></span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>

</div>
