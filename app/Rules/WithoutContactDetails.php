<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WithoutContactDetails implements ValidationRule
{
    private const EMAIL_PATTERN = <<<'REGEX'
        ~(?:[\p{L}\p{N}.!#$%&'*+/=?^_`{|}\x7E-]+|"[^"\r\n]+")
        \h*@\h*[\p{L}\p{N}-]+(?:\h*\.\h*[\p{L}\p{N}-]+)*
        \h*\.\h*(?:\p{L}{2,}|xn--[a-z0-9-]+)(?![\p{L}\p{N}-])~iux
        REGEX;

    private const PHONE_PATTERN = <<<'REGEX'
        ~(?<![\p{L}\p{N}])
        # A date followed by a time can look like a phone number with separators.
        (?![0-3][0-9]([./-])[01][0-9]\1[0-9]{4}\h+[0-9]{1,2}:[0-9]{2})
        (?:
            (?:0|40)[\h()./\p{Pd}]*[23789](?:[\h()./\p{Pd}]*[0-9]){8}
            | (?:\+|00)[\h()./\p{Pd}]*[1-9](?:[\h()./\p{Pd}]*[0-9]){6,14}
        )
        (?![\p{L}\p{N}])~ux
        REGEX;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Normalize a copy for detection. Keep the sender's original text in the form.
        $text = mb_convert_kana(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'as', 'UTF-8');
        $text = preg_replace('/\p{Cf}/u', '', $text);
        $text = preg_replace('/\[\h*at\h*\]|\(\h*at\h*\)/iu', '@', $text);
        $text = preg_replace('/\[\h*(?:dot|punct)\h*\]|\(\h*(?:dot|punct)\h*\)/iu', '.', $text);

        if (preg_match(self::EMAIL_PATTERN, $text) || preg_match(self::PHONE_PATTERN, $text)) {
            $fail('Elimină datele de contact din mesaj. Numerele de telefon și adresele de e-mail nu sunt permise.');
        }
    }
}
