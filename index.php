<?php
        define("APIMODE_TRUE", "true"); // runs in API mode, returns only the converted text without the web-page
        define("SMALL_ALEPH_AYIN", "small"); // small Aleph and Ayin used as default
        
        define("CAPITAL_ALEPH_AYIN", "capital");  // capital Aleph and Ayin used as default
        define("YOD_01310357", "i01310357"); // Yod represented by ı and U+0357
        define("YOD_00690357", "i00690357"); // Yod represented by i and U+0357 
        define("YOD_00690486", "i00690486"); // Yod represented by i and U+0486. Bundled with additional conversions according to conventions used by the BTS, see https://github.com/JKatzwinkel/BTS-Manual and https://github.com/cplutte/bts
        define("YOD_0069032F", "i0069032F"); // Yod represented by i and U+032F
        define("YOD_A7BD", "iA7BD"); // Yod represented by U+A7BD
        define("FORMAT_TRANSLITERATION", "Convert from Transliteration"); // Convert the from ASCII encoding used in the Transliteration font
        define("FORMAT_TRLIT_CG_TIMES", "From Trlit_CG Times");  // Convert the from ASCII encoding used in the Trlit_CG Times font
        define("FORMAT_UMSCHRIFT_TTN", "From Umschrift_TTn");  // Convert the from ASCII encoding used in the Umschrift_TTn font
        define("FORMAT_UNICODE", "From Unicode");  // Convert the from other Unicode encodings 
        define("AMPERSAND_ESCAPE", ""); // The escape code used instead of the & in the web-convertor

        // Enclose each word in the passage containes between the brackets $open and $close in separate tags
        // Example: enclose_all("aa (bb cc) dd", "(", ")") should return "aa (bb) (cc) dd"
        // This function is only needed to format the transliteration for the BTS
        function enclose_all($input, $open, $close) {
            return preg_replace_callback('/(' . preg_quote($open) . '+)(.*?)((?<!' . preg_quote($close) . ')' . preg_quote($close) . '+(?!' . preg_quote($close) . '))/u', "enclose", $input);
        }

        // Auxiliary function used in the previous function
        // This function is only needed to format the transliteration for the BTS
        function enclose($matches) {
            return preg_replace('/\b(\w+)\b/u', $matches[1] . "$1" . $matches[3], $matches[2]);
        }

        // Transliteration adopted in the BTS and TLA requires passages surrounded by brackets to be replaces with passages having each word surrounded by brackets
        // This function formats all types of brackets used in the BTS
        // This function is only needed to format the transliteration for the BTS
        function format_brackets_BTS($input) {
            $res = strtr($input, array_combine(["&lt;", "&gt;"], ["〈", "〉"]));
            $res = enclose_all($res, "[", "]");

            $res = enclose_all($res, "⸮", "?");
            $res = enclose_all($res, "ß", "?");
            $res = enclose_all($res, "(", ")");
            $res = enclose_all($res, "〈", "〉");

            $res = enclose_all($res, "{", "}");
            $res = enclose_all($res, "⸢", "⸣");
            return $res;
        }

        function postformat_brackets_BTS($input) {
            $res = (preg_replace("/([^\s〈({⸮⸢])([〈({⸮⸢]*=)/", "$1 $2", $input));
            $res = (preg_replace("/(=)([\[〈({⸮⸢]+)/", "$2$1", $res));
            return $res;
        }

        // convert_to_unicode is the principal function for converting transliteration to Unicode.
        // It should be used when the code is employed outside the web-converter
        // The function converts escaped $input to Unicode
        // Options:
        // $alephayin - convention for representing Egyptological Aleph and Ayin: either SMALL_ALEPH_AYIN or CAPITAL_ALEPH_AYIN
        // $yod - convention for representing Egyptological Yod: either YOD_01310357 or YOD_00690357 or YOD_00690486 or YOD_0069032F or YOD_A7BD
        // $format - source format: either FORMAT_TRANSLITERATION or FORMAT_TRLIT_CG_TIMES or FORMAT_UMSCHRIFT_TTN or FORMAT_UNICODE
        function convert_to_unicode($input, $alephayin = SMALL_ALEPH_AYIN, $yod = YOD_00690357, $format = FORMAT_TRANSLITERATION) {
            $escaped = htmlspecialchars(str_replace('&', AMPERSAND_ESCAPE, $input));
            $res = convert_escaped_to_unicode($escaped, $alephayin, $yod, $format);
            return str_replace(AMPERSAND_ESCAPE, '&amp;', $res);
        }

        // converts already escaped $input to Unicode
        // it is used in the web-converter, which escapes the ampersand with AMPERSAND_ESCAPE
        // Options:
        // $alephayin - convention for representing Egyptological Aleph and Ayin: either SMALL_ALEPH_AYIN or CAPITAL_ALEPH_AYIN
        // $yod - convention for representing Egyptological Yod: either YOD_01310357 or YOD_00690357 or YOD_00690486 or YOD_0069032F or YOD_A7BD
        // $format - source format: either FORMAT_TRANSLITERATION or FORMAT_TRLIT_CG_TIMES or FORMAT_UMSCHRIFT_TTN or FORMAT_UNICODE
        function convert_escaped_to_unicode($input, $alephayin = SMALL_ALEPH_AYIN, $yod = YOD_00690357, $format = FORMAT_TRANSLITERATION) {
            $kdotsmall = "ḳ";
            $kdotcap = "Ḳ";
            $saccentsmall = "ś";
            $saccentcap = "Ś";
            $equal = "⸗";
            if ($alephayin === SMALL_ALEPH_AYIN) {
                $aleph = "ꜣ";
                $ayin = "ꜥ";
            } else {
                $aleph = "Ꜣ";
                $ayin = "Ꜥ";
            }
            if ($yod === YOD_00690357) {
                $yodsmall = "i͗";
                $yodcap = "I͗";
            } elseif ($yod === YOD_00690486) {
                $yodsmall = "i҆";
                $yodcap = "I҆";
            } elseif ($yod === YOD_0069032F) { //Transliteration adopted in the BTS and TLA
                $yodsmall = "i̯";
                $yodcap = "I̯";
                $equal = "=";
                $kdotsmall = "q";
                $kdotcap = "Q";
                $saccentsmall = "s";
                $saccentcap = "S";
            } elseif ($yod === YOD_01310357) {
                $yodsmall = "ı͗";
                $yodcap = "I͗";
            } elseif ($yod === YOD_A7BD) {
                $yodsmall = "\u{A7BD}";
                $yodcap = "\u{A7BC}";
            }

            //Depending on the source format, two arrays of characters are defined, one with characters to be replaced and the other with the resulting characters 
            if ($format === FORMAT_TRANSLITERATION) {
                $findchars = array('&quot;', 'x', 'A', 'a', 'i', 'H', 'X', 'c', 'S', 'q', 'T', 'D', 'o', '!', '@', '#', '$', '%', '^', '¥', AMPERSAND_ESCAPE, '*', '§', '_', '+', 'Q', 'I', 'O', 'C', 'V', 'v', '=');
                $replacechars = array('&quot;', 'ḫ', $aleph, $ayin, $yodsmall, 'ḥ', 'ẖ', $saccentsmall, 'š', $kdotsmall, 'ṯ', 'ḏ', 'q', 'H', 'Ḥ', 'Ḫ', 'H̱', 'S', 'Š','Š', 'T', 'Ṯ', 'Ṯ','D', 'Ḏ', $kdotcap, $yodcap, 'Q', $saccentcap, 'h̭', 'ṱ', $equal);
            } elseif ($format === FORMAT_TRLIT_CG_TIMES) {
                $findchars = array('&quot;', 'x', 'A', 'a', 'i', 'H', 'X', 'c', 'S', 'q', 'T', 'D', 'o', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'Q', 'I', 'O', 'C', 'V', 'v', AMPERSAND_ESCAPE, '!', 'L', '=');
                $replacechars = array('&quot;', 'ḫ', $aleph, $ayin, $yodsmall, 'ḥ', 'ẖ', $saccentsmall, 'š', $kdotsmall, 'ṯ', 'ḏ', 'q', 'H', 'Ḥ', 'Ḫ', 'H̱', 'S', 'Š', 'T', 'Ṯ', 'D', 'Ḏ', $kdotcap, $yodcap, 'Q', $saccentcap, 'Ṱ', 'ṱ', '&amp;', 'Ú', '⸥', $equal);
            } elseif ($format === FORMAT_UMSCHRIFT_TTN) {
                $findchars = array('&quot;', '~', 'X', '#', 'o', '|', 'H', 'x', 'È', 'Q', 'T', 'D', '!', '@', '$', '%', '^', AMPERSAND_ESCAPE, '_', '+', 'O', 'V', 'v', '=', 'e', 'A', "'", '\\', 'c', '³', '²', 'E', '¢', '¦', '§', 'ß', '¾', 'µ', 'À', '', 'ƒ', '†', '‡', '‰', 'Š', '™', 'š', '¡', '£', '¥', '©', '®', '¯', '°', '±', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ù', 'Ú', 'Ü', 'à', 'á', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'ï', 'ñ', 'ò', 'ó', 'õ', 'ö', 'ô', 'ù', 'ú', 'û', 'ü', 'þ', 'S', 'C', '½', '¼', '¿', 'Ē', 'ł');
                $replacechars = array('Ḥ', 'ï', 'ḫ', $aleph, $ayin, $yodsmall, 'ḥ', 'ẖ', $saccentsmall, $kdotsmall, 'ṯ', 'ḏ', 'H', 'č̣', 'H̱', 'Ḫ', '(', '⸢', 'u̯', 'i̯', 'Ḥ', 'Ṯ', 'T', $equal, 'D', 'ʾ', 'ʾ', '⸣', 'S', 'ṭ', 'č', 'Ḏ', 'Ǧ', 'ı͗', 'h̭', 'ṱ', 'ḍ', 'E', 'A', '|', 'ǧ', 'c', '²', 'T', '~', $aleph, 'ʕ', '_', 'Ṱ', 'e', 'h̭', 'ˉ́', 'ˉ', '˘', '˘́', 'ā́', 'Ẓ', 'ẓ', 'Q', 'Ġ', 'ṭ', 'Č', 'ḗ', $yodcap, '+', '³', 'ī́', $yodsmall, 'ŏ́', 'R̂', 'O', 'o', 'Ṣ', 'ṣ', $kdotcap, 'Č̣', 'ū́', $saccentcap, 'ắ', 'ă', 'ā', 'ġ', 'Ṭ', 'č', 'ĕ́', 'ĕ', 'e', 'ē', 'ĭ́', 'ĭ', 'ī', 'r̂', 'ŏ́', 'ŏ', 'ṓ', 'ō','ṭ', 'ŭ́', 'ŭ', $kdotsmall, 'ū', 'ắ', 'š', 'Š', '(', "\u{1337A}", "\u{1337A}", 'Đ', 'A');
            } elseif ($format === FORMAT_UNICODE) {
                $findchars = array("\u{A7BD}", 'ı̓', 'ı͗', 'ı҆', 'i̓', 'i͗', 'i҆', 'ỉ', "\u{A7BC}", 'I̓', 'I͗', 'I҆', 'Ỉ', 'ꜣ', 'Ꜣ', 'ȝ', 'Ȝ', 'Ꜥ', 'ꜥ', 'ʿ', '', '', '', '', '', '', '', '', '', '');
                $replacechars = array($yodsmall, $yodsmall, $yodsmall, $yodsmall, $yodsmall, $yodsmall, $yodsmall, $yodsmall, $yodcap, $yodcap, $yodcap, $yodcap, $yodcap, $aleph, $aleph, $aleph, $aleph, $ayin, $ayin, $ayin, 'č̣', 'H̱', 'H̭', 'h̭', $aleph, $ayin, 'i̯', 'u̯', $yodsmall, $yodcap);
            }
            if ($yod === YOD_0069032F) { //Transliteration adopted in the BTS and TLA 
                array_push($findchars, "&lt;", "&gt;", "ß", "⸗", "ḳ", "Ḳ", "ś", "Ś", "ṭ", "Ṭ", "č", "Č", "č̣", "Č̣");
                array_push($replacechars, "〈", "〉", "⸮", $equal, $kdotsmall, $kdotcap, $saccentsmall, $saccentcap, "d", "D", "ṯ", "Ṯ", "ḏ", "Ḏ");
            }

            if ($yod === YOD_0069032F && function_exists("format_brackets_BTS")) { //Transliteration adopted in the BTS and TLA requires passages surrounded by brackets to be replaces with passages having each word surrounded by brackets
                $input = format_brackets_BTS($input);
            }
            $res = strtr($input, array_combine($findchars, $replacechars)); //This is the principal line, the conversion takes place here

            if ($yod === YOD_0069032F && function_exists("postformat_brackets_BTS")) { //Transliteration adopted in the BTS and TLA requires passages surrounded by brackets to be replaces with passages having each word surrounded by brackets
                $res = postformat_brackets_BTS($res);
            }
            return $res;
        }

// Here begins the code used to process the POST and GET parameters and to render the web-page of the online Unicode converter
// The script accepts both POST and GET arguments        
        if (isset($_POST["input"])) {
            $format = filter_input(INPUT_POST, 'format', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            $apimode = filter_input(INPUT_POST, 'apimode', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);            
            $alephayin = filter_input(INPUT_POST, 'alephayin', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            $yod = filter_input(INPUT_POST, 'yod', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            $input = htmlspecialchars(str_replace('&', AMPERSAND_ESCAPE, $_POST["input"]));
        } elseif (isset($_GET["input"]) || isset($_GET["yod"])) {
            $format = filter_input(INPUT_GET, 'format', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            $apimode = filter_input(INPUT_GET, 'apimode', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);            
            $alephayin = filter_input(INPUT_GET, 'alephayin', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            $yod = filter_input(INPUT_GET, 'yod', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
            $input = htmlspecialchars(str_replace('&', AMPERSAND_ESCAPE, $_GET["input"]));
        } else {
            $format = $alephayin = $yod = $apimode = $input = "";
        }
        $conversion_result = convert_escaped_to_unicode($input, $alephayin, $yod, $format);
        if ($apimode === APIMODE_TRUE){
            
            exit (trim($conversion_result));
            //exit();
        }
        ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="stylesheet.css" rel="stylesheet">
        <title>Transliteration to Unicode Converter</title>
    </head>
    <body>
        <div class=limit><h2>Transliteration to Unicode Converter</h2><p>
            <h3>Please enter a passage with Egyptian transliteration below</h3>
        </div><form action='index.php' method='post'>
            <div class=limit><textarea name="input" rows="5" spellcheck="false" <?php
                if (empty($input)) {
                    echo(" autofocus ");
                }
                ?> style="width:100%; font-family:Roboto, New Athena Unicode; letter-spacing: 0.2px;"><?php
                                       echo (str_replace(AMPERSAND_ESCAPE, '&amp;', $input) );
                                       ?></textarea></div>
            <div class=limit style='padding-bottom: 3px;'> 
                <input type="radio" id="<?= SMALL_ALEPH_AYIN ?>" name="alephayin" tabindex="-1" value="<?= SMALL_ALEPH_AYIN ?>"<?php
                if (empty($alephayin) || $alephayin === SMALL_ALEPH_AYIN) {
                    echo(" checked");
                }
                ?>> <label for="<?= SMALL_ALEPH_AYIN ?>"><span title="used in the JSesh keyboard layouts and the Ramses project">Small <span class="trlit">ꜣ</span> and <span class="trlit">ꜥ</span></span></label>
                <input type="radio" id="<?= CAPITAL_ALEPH_AYIN ?>" name="alephayin" tabindex="-1" value="<?= CAPITAL_ALEPH_AYIN ?>"<?php
                if ($alephayin === CAPITAL_ALEPH_AYIN) {
                    echo(" checked");
                }
                ?>> <label for="<?= CAPITAL_ALEPH_AYIN ?>"><span title="looks better in most fonts">Capital <span class="trlit">Ꜣ</span> and <span class="trlit">Ꜥ</span></span></label>
                (<a href="#popup1" tabindex="-1">details</a>)
                <div id="popup1" class="overlay">
                    <div class="popup">
                        <a class="close" href="#" tabindex="-1" >&times;</a>
                        <div class="content">
                            <h2>Ꜣ and ꜥ</h2>
                            The Unicode standard makes provision for separate characters for capital and small Egyptological aleph and ayin.
                            This is at odds with modern practice, not least because non-Unicode fonts do not distinguish between these characters.
                            Hence, D.&nbsp;A.&nbsp;Werning earlier suggested using capital aleph and ayin in all positions, as capital aleph and ayin look better in most modern fonts. This practice is followed in the <a href="http://elephantine.smb.museum">Rubensohn-Bibliothek</a>.
                            However, in earlier Egyptological publications and most notably in A.&nbsp;H.&nbsp;Gardiner’s Egyptian Grammar, capital and small forms are distinguished from one another, as <a href="http://evertype.com/pipermail/egyptian_evertype.com/2007-December.txt">pointed out by W.&nbsp;Schenkel</a>.
                            Besides, distinguishing between lower- and upper-case characters is deemed semantically more correct than always using capital letters. Hence, the <a href="http://ramses.ulg.ac.be/">Ramses Project</a> and <a href="http://totenbuch.awk.nrw.de/register/besitzer">Totenbucharchiv</a> use small aleph and ayin. Werning adopts this practice in the modern version (2021) of his convention.
                        </div>
                    </div>
                </div>
            </div>
            <div class=limit style='padding-bottom: 3px;'>  <input type="radio" id="<?= YOD_01310357 ?>" name="yod" tabindex="-1" value="<?= YOD_01310357 ?>"<?php
                if ($yod == YOD_01310357) {
                    echo(" checked");
                }
                ?>> <label for="<?= YOD_01310357 ?>"><span title="as defined by Werning in 2017">i > <span class="trlit">ı͗</span> = ı and U+0357</span></label>
                <input type="radio" id="<?= YOD_00690357 ?>" name="yod" tabindex="-1" value="<?= YOD_00690357 ?>"<?php
                if ( $yod === YOD_00690357) {
                    echo(" checked");
                }
                ?>> <label for="<?= YOD_00690357 ?>"><span title="as defined by Werning in 2018 and used in the Totenbucharchiv database">i > <span class="trlit">i͗</span> = i and U+0357</span></label>
                <input type="radio" id="<?= YOD_00690486 ?>" name="yod" tabindex="-1" value="<?= YOD_00690486 ?>"<?php
                if ($yod === YOD_00690486) {
                    echo(" checked");
                }
                ?>> <label for="<?= YOD_00690486 ?>"><span title="used in JSesh keyboard layouts">i > <span class="trlit">i҆</span> = i and U+0486</span></label>
                <input type="radio" id="<?= YOD_0069032F ?>" name="yod" tabindex="-1" value="<?= YOD_0069032F ?>"<?php
                if ($yod === YOD_0069032F) {
                    echo(" checked");
                }
                ?>> <label for="<?= YOD_0069032F ?>"><span title="used in TLA">i > <span class="trlit">i̯</span> = i and U+032F</span></label>
                <input type="radio" id="<?= YOD_A7BD ?>" name="yod" tabindex="-1" value="<?= YOD_A7BD ?>"<?php
                if (empty($yod) || $yod === YOD_A7BD) {
                    echo(" checked");
                }
                ?>> <label for="<?= YOD_A7BD ?>"><span title="as defined by Werning in 2021 and according to Unicode 12.0">i > <span class="trlit">&#xA7BD;</span> = U+A7BD</span></label>


                (<a href="#popup2" tabindex="-1" >details</a>)
                <div id="popup2" class="overlay">
                    <div class="popup">
                        <a class="close" href="#" tabindex="-1" >&times;</a>
                        <div class="content">
                            <h2>&#xA7BD;</h2>
                            The encoding of the Egyptological yod in Unicode remains an unsettled issue. Generally, one of the three combining signs U+0313, U+0357, or U+0486 can be used to transform an i into yod. 
                            <br>In 2017, D.&nbsp;A.&nbsp;Werning recommended the use of the dotless ı (U+0131) in combination with U+0357, and this recommendation was adopted in the <a href="https://aaew.bbaw.de/berlin-text-system"><i>Berlin Text System</i> (BTS)</a> of the <a href="https://thesaurus-linguae-aegyptiae.de"><i>Thesaurus Linguae Aegyptiae</i></a> and was recommended on the <a href="http://www.stoa.org/epidoc/gl/latest/app-epi-egyptology.html">EpiDoc page</a>.
                            However, the official Unicode FAQ then recommended using the ordinary i (U+0069) as base for any of the three combining diacritic characters.
                            <br>The ordinary i is used in combination with U+0357 in the <a href="http://totenbuch.awk.nrw.de/register/besitzer">Totenbucharchiv</a> and is <a href="http://ucbclassics.dreamhosters.com/djm/pdfs/AboutDemoticEgyptianUnicode09.pdf">recommended by D.&nbsp;Mastronarde</a>. Werning advocated this approach in the 2018 version of his recommendations.
                            <br>Another widely accepted approach is the use of the ordinary i in combination with U+0486. This combination was used in <a href="https://jsesh.qenherkhopeshef.org/varia/transliteration">the keyboard layouts by S.&nbsp;Rosmorduc</a>  and recommended by <a href="https://brill.com/fileasset/downloads_static/static_typefacedownload_typefaceuserguide.pdf">Brill Publishers</a>.

                            <br>
                            <br>Unicode 12.0 (March 2019) defines new characters for Egyptological Yod: <span class="trlit">&#xA7BD;</span> = U+A7BD (Latin small letter glottal I) and <span class="trlit">&#xA7BC;</span> = U+A7BC (Latin capital letter glottal I).
                            This should become the new standard. As of February 2022, these characters are  supported by a number of freely available fonts, including <a href="https://software.sil.org/andika/download/">Andika</a>, <a href="https://scripts.sil.org/ttw/fonts2go.cgi?family=Andika&pkg=Compact">Andika Compact</a>, <a href="https://software.sil.org/charis/download/">Charis</a>, <a href="https://software.sil.org/doulos/download/">Doulos</a>, and <a href="https://software.sil.org/gentium/download/">Gentium Plus</a> by SIL, <a href="https://apagreekkeys.org/NAUdownload.html">New Athena Unicode</a> and <a href="https://notofonts.github.io/#latin-greek-cyrillic">recent versions of Noto Serif</a>. <a href="https://brill.com/page/290?language=en">The Brill Typeset 4.0</a> also supports these and other transliteration characters; however, it only free for non-commercial use. Full support is also provided by Calibri 6.26 and Tahoma 7.01, supplied with Microsoft Windows 11 and Microsoft Office. See <a href="Fonts%20with%20A7BD.pdf">a comparison of these fonts</a>. 
                            <br>The use of U+A7BD and U+A7BC is the <a href="https://aaew.bbaw.de/egyptological-unicode-fonts">current recommendation by D.&nbsp;A.&nbsp;Werning</a>.
                            <br>Unlike the capital and small variants of aleph and ayin, different encodings of yod are despite similar outlook mutually incompatible; as per <a href="http://unicode.org/faq/char_combmark.html#21">the official Unicode FAQ</a>, computer software considers them completely different signs, not variants of the same sign.
                            <h2>i̯</h2>
                            <span class="trlit">i̯</span>  (i and U+032F) is used in the <i>Berlin Text System</i> (BTS) to encode weak last consonants in verbs. It corresponds to i in the non-Unicode online version of the <a href="http://aaew.bbaw.de/tla/servlet/TlaLogin"><i>Thesaurus Linguae Aegyptiae</i></a>. With this option selected, the Converter also makes other transformations to make the transliteration compatible with the BTS.
                            
                        </div>
                    </div>
                </div>
            </div>
            <div class=limit style='padding-bottom: 3px;'>
                <input type='submit' name='format' value='<?= FORMAT_TRANSLITERATION ?>' title="Convert from the Transliteration font">
                <input type='submit' name='format' value='<?= FORMAT_TRLIT_CG_TIMES ?>' title="Convert from the Trlit_CG Times font">
                <input type='submit' name='format' value='<?= FORMAT_UMSCHRIFT_TTN ?>' title="Convert from the Umschrift_TTn font">
                <input type='submit' name='format' value='<?= FORMAT_UNICODE ?>' title="Convert from other versions of Unicode">
            </div></form>
        <?php
        if (!empty($input)) {
            ?>
            <div><h3>The same passage in Unicode converted from <?php
                    switch ($format) {
                        case FORMAT_TRANSLITERATION:
                            echo "Transliteration";
                            break;
                        case FORMAT_TRLIT_CG_TIMES:
                            echo "Trlit_CG Times";
                            break;
                        case FORMAT_UMSCHRIFT_TTN:
                            echo "Umschrift_TTn";
                            break;
                        case FORMAT_UNICODE:
                            echo "Unicode";
                            break;
                    }
                    ?></h3>
                <?php ?>
                <script src="clipboard.min.js"></script>
                <script>
                    var clipboard = new Clipboard('.btn');
                    clipboard.on('success', function (e) {
                        console.log(e);
                    });
                    clipboard.on('error', function (e) {
                        console.log(e);
                    });
                </script>
                <div class=limit><p><textarea name="output" id="out" spellcheck="false" autofocus rows="5" style="width:100%; font-family:Noto Serif; font-style: italic; letter-spacing: 0.2px;"><?= str_replace(AMPERSAND_ESCAPE, '&amp;', $conversion_result) ?></textarea></p>
                                        <button class="btn" data-clipboard-target="#out"> Copy to clipboard</button></div>
                                                                                                                                                                        </div>
            <?php
        }
        ?>
        <div class=limit style='padding-top: 18px;'>  This page converts Egyptian transliteration passages set in non-Unicode fonts into Unicode
            following the conventions outlined by <a href="http://hdl.handle.net/21.11101/0000-0000-9E1A-2"> D.&nbsp;A.&nbsp;Werning</a> and <a href="http://ucbclassics.dreamhosters.com/djm/pdfs/AboutDemoticEgyptianUnicode09.pdf">D.&nbsp;Mastronarde</a> and used in <a href="https://thesaurus-linguae-aegyptiae.de">Thesaurus Linguae Aegyptiae v2.01</a>, <a href='http://totenbuch.awk.nrw.de/'>Totenbucharchiv</a>, <a href='http://ramses.ulg.ac.be/'>Ramses</a>, <a href="https://sae.saw-leipzig.de/en">Science in Ancient Egypt</a> and other digital Egyptological projects as well as by some of the publishers. 
            See <a href='Fonts%20with%20A7BD.pdf'>a comparison of compatible fonts</a>.
                <br>This converter supports the encoding schemes used in the fonts Transliteration (CCER), Trlit_CG Times (<a href="https://dmd.wepwawet.nl/fonts.htm">The Deir el-Medina Database</a>), and Umschrift_TTn (<a href="http://wwwuser.gwdg.de/~lingaeg/lingaeg-stylesheet.htm">F.&nbsp;Junge/Universität Göttingen</a>) as well as different Unicode schemes as input.
                <br>
                A <a href ="http://www.ifao.egnet.net/publications/publier/outils-ed/convertisseurs/">similar converter by IFAO</a> uses a different (older) convention for the representation of the Egyptian transliteration signs aleph, ayin, and yod in the Unicode. 
                The IFAO convention is <a href="#popup3" tabindex="-1">widely used in Egyptological projects</a>.
                <div id="popup3" class="overlay">
                    <div class="popup">
                        <a class="close" href="#" tabindex="-1" >&times;</a>
                        <div class="content">
                            <h2>IFAO Unicode</h2>
                            The IFAO flavour of Unicode transliteration is adopted by <a href="http://www.trismegistos.org/ref/index.php">Trismegistos</a>, <a href="http://www.ifao.egnet.net/bases/cachette/">Cachette de Karnak</a>, <a href="http://www.ifao.egnet.net/bases/agea/">AGÉA</a>, and <a href="http://vega-vocabulaire-egyptien-ancien.fr/">VÉgA</a> among other digital projects.
                        </div>
                    </div>
                </div>
                In the “From Unicode” mode this page can convert the IFAO Unicode as well as the <a href="https://apagreekkeys.org/technicalDetails.html">private use characters used only in the New Athena Unicode font</a> to the convention used on this page.
                Another <a href="http://helmwo.net/Umschrift/README.html">tool by H.&nbsp;Wodtke</a> uses italicised mathematical symbols instead of Latin letters to make transliterated passages look cursive without changing the font style (thus, the result is incompatible with any of the current encoding conventions). 
</div>  
        <div>Source code <a href="https://github.com/ailintom/UnicodeConverter">available on GitHub</a>.
          <br>  <br> <a href="#popup4" tabindex="-1" >About / Impressum</a></div>       
                        <div id="popup4" class="overlay">
                    <div class="popup">
                        <a class="close" href="#" tabindex="-1" >&times;</a>
                        <div class="content">
                            <h2>About / Impressum</h2>
    <p>
Author and responsible person: <a href = "https://www.aegyptologie.uni-mainz.de/ilin-tomich/">Alexander Ilin-Tomich</a>
</p>
<p>
Institut für Altertumswissenschaften, Ägyptologie
<br>
FB 07, Johannes Gutenberg-Universität Mainz
<br>
55099 Mainz, Deutschland
</p>
<p>
Tel.: +4961313938345
<br>
E-Mail: <a href = "mailto:ailintom@uni-mainz.de">ailintom@uni-mainz.de</a>
</p>
<h2>Acknowledgements</h2>I am grateful to D.&nbsp;A.&nbsp;Werning, T.&nbsp;Konrad, and A.&nbsp;von Lieven for valuable corrections to this page.
                        </div>
                    </div>
                </div>
</body>
</html>
