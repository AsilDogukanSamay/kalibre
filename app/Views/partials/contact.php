<?php /** @var array<string,mixed> $brand */ ?>
<section id="iletisim" class="section shell">
    <div class="grid gap-8 lg:grid-cols-12 lg:gap-14">

        <div class="flex flex-col gap-6 lg:col-span-5" data-reveal>
            <div class="section-head section-head-brand mb-0" data-reveal>
                <span class="eyebrow-label eyebrow-brand">Randevu</span>
                <h2 class="h-section text-balance" data-satir>Önce aracı görelim.</h2>
                <p class="body-text">Ölçüm ve durum tespiti ücretsiz, yaklaşık 45 dakika sürüyor. Formu bırakın, aynı gün içinde arıyoruz.</p>
            </div>

            <dl class="flex flex-col gap-4">
                <div>
                    <dt class="text-xs uppercase tracking-[0.14em] text-ink-faint">Atölye</dt>
                    <dd class="mt-1 max-w-[42ch] text-sm text-ink"><?= e($brand['address']) ?></dd>
                    <dd class="mt-1.5">
                        <a class="link-ic inline-flex items-center gap-1.5 text-sm"
                           href="<?= e($brand['maps']) ?>" target="_blank" rel="noopener">
                            Haritada gör
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M11 3h6v6h-2V6.4l-7.3 7.3-1.4-1.4L13.6 5H11V3z"/><path d="M5 5h4V3H3v14h14v-6h-2v4H5V5z"/></svg>
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-[0.14em] text-ink-faint">Telefon</dt>
                    <dd class="mt-1"><a class="text-sm text-ink transition hover:text-brand-text" href="tel:<?= e(str_replace(' ', '', $brand['phone'])) ?>"><?= e($brand['phone']) ?></a></dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-[0.14em] text-ink-faint">Çalışma saatleri</dt>
                    <dd class="mt-1 text-sm text-ink"><?= e($brand['hours']) ?></dd>
                </div>
            </dl>
        </div>

        <div class="lg:col-span-7" data-reveal>
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
                        <p class="field-hint">Aynı gün içinde bu numaradan arıyoruz.</p>
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

                <div class="field">
                    <div class="consent">
                        <input class="consent-box" id="consent" name="consent" type="checkbox" value="1" required>
                        <label class="consent-text" for="consent">
                            <a class="legal-link" href="/kvkk">KVKK Aydınlatma Metni</a>'ni okudum; ad, telefon
                            ve e-posta bilgimin randevu talebim için işlenmesini kabul ediyorum.
                        </label>
                    </div>
                    <p class="field-error" data-error-for="consent"></p>
                </div>

                <button class="btn-primary btn-block sm:w-fit" type="submit" data-submit>
                    <span data-submit-label>Talebi gönder</span>
                    <svg class="hidden h-4 w-4 animate-spin" data-submit-spinner viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4z"/>
                    </svg>
                </button>

                <p class="note max-w-[62ch]">
                    Gönderdiğiniz bilgiler yalnızca randevu için kullanılır, üçüncü taraflarla paylaşılmaz.
                    Bu sitede çerez kullanılmaz &mdash; ayrıntı için
                    <a class="legal-link" href="/gizlilik">gizlilik ve çerez politikası</a>.
                </p>
            </form>
        </div>

    </div>
</section>
