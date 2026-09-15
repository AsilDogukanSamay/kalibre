<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Kural tabanli dogrulama. Kurallar "required|email|min:3" bicimindedir.
 * Her alan icin tek hata mesaji doner, frontend alan adina gore eslestirir.
 */
final class Validator
{
    /** @var array<string,string> */
    private array $errors = [];

    /** @var array<string,string> */
    private array $clean = [];

    /**
     * @param array<string,mixed>  $data
     * @param array<string,string> $rules
     */
    public function __construct(private array $data, private array $rules)
    {
    }

    public function passes(): bool
    {
        foreach ($this->rules as $field => $ruleset) {
            $raw = $this->data[$field] ?? '';
            $value = is_scalar($raw) ? trim((string) $raw) : '';

            // Kontrol karakterlerini bastan temizle
            $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';
            $this->clean[$field] = $value;

            foreach (explode('|', $ruleset) as $rule) {
                if ($this->failsRule($field, $value, $rule)) {
                    break; // alan basina tek mesaj
                }
            }
        }

        return $this->errors === [];
    }

    private function failsRule(string $field, string $value, string $rule): bool
    {
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

        $fail = function (string $message) use ($field): bool {
            $this->errors[$field] = $message;
            return true;
        };

        return match ($name) {
            'required' => $value === '' ? $fail('Bu alan zorunludur.') : false,

            'email' => $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)
                ? $fail('Geçerli bir e-posta adresi girin.')
                : false,

            'phone' => $value !== '' && preg_match('/^[0-9+()\s-]{10,20}$/', $value) !== 1
                ? $fail('Geçerli bir telefon numarası girin.')
                : false,

            'min' => $value !== '' && mb_strlen($value) < (int) $param
                ? $fail(sprintf('En az %d karakter olmalıdır.', (int) $param))
                : false,

            'max' => mb_strlen($value) > (int) $param
                ? $fail(sprintf('En fazla %d karakter olabilir.', (int) $param))
                : false,

            default => false,
        };
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    /** Dogrulanmis, kirpilmis degerler. XSS kacisi ciktida yapilir. */
    public function validated(): array
    {
        return $this->clean;
    }
}
