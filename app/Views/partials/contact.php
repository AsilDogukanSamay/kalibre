<?php /** @var array<string,mixed> $brand */ ?>
<section id="iletisim" class="section shell">
    <div class="grid gap-8 lg:grid-cols-12 lg:gap-14">

        <div class="flex flex-col gap-6 lg:col-span-5">
            <div class="section-head mb-0">
                <h2 class="h-section text-balance">Önce aracı görelim.</h2>
                <p class="body-text">Ölçüm ve durum tespiti ücretsiz, yaklaşık 45 dakika sürüyor. Formu bırakın, aynı gün içinde arıyoruz.</p>
            </div>

            <dl class="flex flex-col gap-4">
                <div>
                    <dt class="text-xs uppercase tracking-[0.14em] text-ink-faint">Atölye</dt>
                    <dd class="mt-1 text-sm text-ink"><?= e($brand['address']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-[0.14em] text-ink-faint">Telefon</dt>
                    <dd class="mt-1"><a class="text-sm text-ink transition hover:text-brand" href="tel:<?= e(str_replace(' ', '', $brand['phone'])) ?>"><?= e($brand['phone']) ?></a></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-[0.14em] text-ink-faint">Çalışma saatleri</dt>
                    <dd class="mt-1 text-sm text-ink"><?= e($brand['hours']) ?></dd>
                </div>
            </dl>
        </div>

        <div class="lg:col-span-7">
            <form class="glass-strong glass-sheen flex flex-col gap-5 p-6 sm:p-8" id="contactForm" novalidate>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="field">
                        <label class="field-label" for="full_name">Ad soyad</label>
                        <input class="field-input" id="full_name" name="full_name" type="text" autocomplete="name" required>
                        <p class="field-error" data-error-for="full_name"></p>
                    </div>

                    <div class="field">
                        <label class="field-label" for="phone">Telefon</label>
                        <input class="field-input" id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required>
                        <p class="field-hint">Ayni gun icinde bu numaradan ariyoruz.</p>
                        <p class="field-error" data-error-for="phone"></p>
                    </div>
                </div>

                <div class="field">
                    <label class="field-label" for="email">E-posta</label>
                    <input class="field-input" id="email" name="email" type="email" autocomplete="email" required>
                    <p class="field-error" data-error-for="email"></p>
                </div>

                <div class="field">
                    <label class="field-label" for="message">Mesajınız</label>
                    <textarea class="field-input min-h-[8rem] resize-y" id="message" name="message" rows="4" placeholder="Araç marka ve modeli, boyadaki durum, varsa lokal hasarlar." required></textarea>
                    <p class="field-error" data-error-for="message"></p>
                </div>

                <div class="honeypot" aria-hidden="true">
                    <label for="website">Bu alanı boş bırakın</label>
                    <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                </div>

                <button class="btn-primary btn-block sm:w-fit" type="submit" data-submit>
                    <span data-submit-label>Talebi gönder</span>
                    <svg class="hidden h-4 w-4 animate-spin" data-submit-spinner viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4z"/>
                    </svg>
                </button>

                <p class="field-hint">Gönderdiğiniz bilgiler yalnızca randevu için kullanılır, üçüncü taraflarla paylaşılmaz.</p>
            </form>
        </div>

    </div>
</section>
