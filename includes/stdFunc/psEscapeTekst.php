<?php
//                ___   _   _   ___  _     ___  _ _
//               / __| / \ | | |   \| |   |   \| / /
//               \__ \/ _ \| |_| |) | | _ | |) |  <
//               |___/_/ \_|___|___/|_||_||___/|_\_\
//
// --- includes/stdFunc/psEscapeTekst.php --- patch 5.0.0 --- 2026-09-16 ---
// LICENS
//
// This program is free software. You can redistribute it and / or
// modify it under the terms of the GNU General Public License (GPL)
// which is published by The Free Software Foundation; either in version 2
// of this license or later version of your choice.
// However, respect the following:
//
// It is forbidden to use this program in competition with Saldi.DK ApS
// or other proprietor of the program without prior written agreement.
//
// The program is published with the hope that it will be beneficial,
// but WITHOUT ANY KIND OF CLAIM OR WARRANTY. See
// GNU General Public License for more details.
//
// Copyright (c) 2003-2026 saldi.dk aps
// ----------------------------------------------------------------------
// 20260916 CDX/MJ SST-784 Extracted from includes/formfunk.php so both PostScript write sites
//                  share one escape, and so it can be tested without including formfunk.php
//                  (which runs database and mail code at include time).

if (!function_exists('ps_escape_tekst')) {
	/**
	 * Escapes a string for use inside a PostScript string literal.
	 *
	 * PostScript delimits strings with ( and ), so a literal parenthesis has to be escaped, and
	 * a backslash before it, or the backslash would escape whatever follows.
	 *
	 * An UNBALANCED parenthesis is what actually breaks a print. A balanced pair is legal inside
	 * a PostScript string and renders correctly, which is why an unescaped write went unnoticed;
	 * with a value like "Firma (afd" ps2pdf aborts with /syntaxerror and no file is produced.
	 *
	 * Deliberately not api/send_invoice_pdf.php's ps_escape(): that one also strips every byte
	 * outside \x20-\x7E, which would delete ae/oe/aa. Text reaching this function has already
	 * been through utf8_iso8859(), so high bytes are meaningful and must survive untouched.
	 *
	 * strtr() with an array replaces in a single pass, so a backslash that has just been escaped
	 * is not escaped again. Sequential str_replace() calls would double it.
	 *
	 * @param string $tekst Text already converted to the output encoding.
	 * @return string
	 */
	function ps_escape_tekst($tekst)
	{
		return strtr((string) $tekst, array('\\' => '\\\\', '(' => '\\(', ')' => '\\)'));
	}
}
?>
