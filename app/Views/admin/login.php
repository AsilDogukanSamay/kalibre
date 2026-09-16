<?php
/**
 * @var string      $csrf
 * @var string|null $error
 * @var int         $remaining
 */
?>
<div class="admin-shell">
    <form class="login-card" method="post" action="/yonetim/giris">
        <div class="flex flex-col gap-2">
            <p class="eyebrow-label">Yönetim paneli</p>
            <h1 class="h-card">Giriş yapın</h1>
            <p class="note">Gelen randevu talepleri bu panelde listelenir.</p>
        </div>

        <?php if ($error !== null): ?>
            <p class="glass-soft border-warn/45 bg-warn/[0.10] p-3 text-sm text-warn" role="alert">
                <?= e($error) ?>
            </p>
        <?php endif; ?>

        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">

        <div class="field">
            <label class="field-label" for="user">Kullanıcı adı</label>
            <input class="field-input" id="user" name="user" type="text" autocomplete="username" required autofocus>
        </div>

        <div class="field">
            <label class="field-label" for="password">Parola</label>
            <input class="field-input" id="password" name="password" type="password" autocomplete="current-password" required>
        </div>

        <button class="btn-primary btn-block" type="submit">Giriş yap</button>

        <p class="note">
            Kalan deneme hakkı: <?= e((string) $remaining) ?>.
            Hak bittiğinde giriş 15 dakika kilitlenir.
        </p>
    </form>
</div>
