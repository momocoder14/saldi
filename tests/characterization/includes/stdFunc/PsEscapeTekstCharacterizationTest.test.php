<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../includes/stdFunc/psEscapeTekst.php';

// 20260916 CDX/MJ SST-784 Pins the escaping both of skriv()'s PostScript write sites depend on.
final class PsEscapeTekstCharacterizationTest extends TestCase
{
    /** One literal backslash. Built rather than written inline, so the intent stays readable. */
    private const BS = '\\';

    #[DataProvider('parenCases')]
    public function testParenthesesAreEscaped(string $input, string $expected): void
    {
        self::assertSame($expected, ps_escape_tekst($input));
    }

    public static function parenCases(): array
    {
        return [
            // A balanced pair is legal PostScript and renders fine - which is why the unescaped
            // write went unnoticed for so long. It still has to be escaped.
            'balanced pair'    => ['Auto-Lauge (Noerager) ApS', 'Auto-Lauge \(Noerager\) ApS'],
            // The one that actually breaks the print: ps2pdf aborts with /syntaxerror.
            'unbalanced open'  => ['Auto-Lauge (Noerager ApS', 'Auto-Lauge \(Noerager ApS'],
            'unbalanced close' => ['Smith & Sons ) ApS', 'Smith & Sons \) ApS'],
            'nested'           => ['Nested (outer (inner))', 'Nested \(outer \(inner\)\)'],
            'plain'            => ['Plain text', 'Plain text'],
            'empty'            => ['', ''],
        ];
    }

    /** A backslash must be escaped too, or it would escape the character after it. */
    public function testBackslashIsEscapedFirst(): void
    {
        $bs = self::BS;
        self::assertSame($bs . $bs, ps_escape_tekst($bs));
        self::assertSame('C:' . $bs . $bs . 'new', ps_escape_tekst('C:' . $bs . 'new'));
    }

    /** Single pass: a backslash that was just escaped is not escaped again. */
    public function testSinglePassDoesNotDoubleEscape(): void
    {
        $bs = self::BS;
        // '\(' -> the backslash becomes '\\', the paren becomes '\(' => '\\' . '\('
        self::assertSame($bs . $bs . $bs . '(', ps_escape_tekst($bs . '('));
        // two backslashes + '(' -> four backslashes + '\('
        self::assertSame(str_repeat($bs, 5) . '(', ps_escape_tekst($bs . $bs . '('));
    }

    /** High bytes are meaningful after utf8_iso8859() and must survive untouched. */
    public function testIso8859HighBytesArePreserved(): void
    {
        $iso = iconv('UTF-8', 'ISO-8859-15', 'Nørager (afd) ApS');
        self::assertSame(str_replace(['(', ')'], [self::BS . '(', self::BS . ')'], $iso), ps_escape_tekst($iso));
    }

    /** Both PostScript write sites in skriv() must route through this helper, not just one. */
    public function testBothWriteSitesUseTheHelper(): void
    {
        $src = file_get_contents(__DIR__ . '/../../../../includes/formfunk.php');
        self::assertSame(
            2,
            substr_count($src, 'ps_escape_tekst(utf8_iso8859('),
            'formfunk.php should escape both the order-line text and the form-variable write'
        );
    }
}
