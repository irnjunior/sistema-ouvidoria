<?php
/*********************************************************************
    class.format.php

    Collection of helper function used for formatting

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

include_once INCLUDE_DIR.'class.charset.php';

class Format {


    function file_size($bytes) {

        if(!is_numeric($bytes))
            return $bytes;
        if($bytes<1024)
            return $bytes.' bytes';
        if($bytes < (900<<10))
            return round(($bytes/1024),1).' kb';

        return round(($bytes/1048576),1).' mb';
    }

    function filesize2bytes($size) {
        switch (substr($size, -1)) {
        case 'M': case 'm': return (int)$size <<= 20;
        case 'K': case 'k': return (int)$size <<= 10;
        case 'G': case 'g': return (int)$size <<= 30;
        }

        return $size;
    }

    function mimedecode($text, $encoding='UTF-8') {

        if(function_exists('imap_mime_header_decode')
                && ($parts = imap_mime_header_decode($text))) {
            $str ='';
            foreach ($parts as $part)
                $str.= Charset::transcode($part->text, $part->charset, $encoding);

            $text = $str;
        } elseif($text[0] == '=' && function_exists('iconv_mime_decode')) {
            $text = iconv_mime_decode($text, 0, $encoding);
        } elseif(!strcasecmp($encoding, 'utf-8')
                && function_exists('imap_utf8')) {
            $text = imap_utf8($text);
        }

        return $text;
    }

    /**
     * Decodes filenames given in the content-disposition header according
     * to RFC5987, such as filename*=utf-8''filename.png. Note that the
     * language sub-component is defined in RFC5646, and that the filename
     * is URL encoded (in the charset specified)
     */
    function decodeRfc5987($filename) {
        $match = array();
        if (preg_match("/([\w!#$%&+^_`{}~-]+)'([\w-]*)'(.*)$/",
                $filename, $match))
            // XXX: Currently we don't care about the language component.
            //      The  encoding hint is sufficient.
            return Charset::utf8(urldecode($match[3]), $match[1]);
        else
            return $filename;
    }

    /**
     * Json Encoder
     *
     */
    function json_encode($what) {
        require_once (INCLUDE_DIR.'class.json.php');
        return JsonDataEncoder::encode($what);
    }

	function phone($phone) {

		$stripped= preg_replace("/[^0-9]/", "", $phone);
		if(strlen($stripped) == 7)
			return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2",$stripped);
		elseif(strlen($stripped) == 10)
			return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3",$stripped);
		else
			return $phone;
	}

    function truncate($string,$len,$hard=false) {

        if(!$len || $len>strlen($string))
            return $string;

        $string = substr($string,0,$len);

        return $hard?$string:(substr($string,0,strrpos($string,' ')).' ...');
    }

    function strip_slashes($var) {
        return is_array($var)?array_map(array('Format','strip_slashes'),$var):stripslashes($var);
    }

    function wrap($text, $len=75) {
        return $len ? wordwrap($text, $len, "\n", true) : $text;
    }

    function html_balance($html, $remove_empty=true) {
        if (!extension_loaded('dom'))
            return $html;

        if (!trim($html))
            return $html;

        $doc = new DomDocument();
        $xhtml = '<?xml encoding="utf-8"><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>'
            // Wrap the content in a <div> because libxml would use a <p>
            . "<div>$html</div>";
        $doc->encoding = 'utf-8';
        $doc->preserveWhitespace = false;
        $doc->recover = true;
        if (false === @$doc->loadHTML($xhtml))
            return $html;

        if ($remove_empty) {
            // Remove empty nodes
            $xpath = new DOMXPath($doc);
            static $eE = array('area'=>1, 'br'=>1, 'col'=>1, 'embed'=>1,
                    'iframe' => 1, 'hr'=>1, 'img'=>1, 'input'=>1,
                    'isindex'=>1, 'param'=>1);
            do {
                $done = true;
                $nodes = $xpath->query('//*[not(text()) and not(node())]');
                foreach ($nodes as $n) {
                    if (isset($eE[$n->nodeName]))
                        continue;
                    $n->parentNode->removeChild($n);
                    $done = false;
                }
            } while (!$done);
        }

        static $phpversion;
        if (!isset($phpversion))
            $phpversion = phpversion();

        $body = $doc->getElementsByTagName('body');
        if (!$body->length)
            return $html;

        if ($phpversion > '5.3.6') {
            $html = $doc->saveHTML($doc->getElementsByTagName('body')->item(0)->firstChild);
        }
        else {
            $html = $doc->saveHTML();
            $html = preg_replace('`^<!DOCTYPE.+?>|<\?xml .+?>|</?html>|</?body>|</?head>|<meta .+?/?>`', '', $html); # <?php
        }
        return preg_replace('`^<div>|</div>$`', '', trim($html));
    }

    function html($html, $config=array()) {
        require_once(INCLUDE_DIR.'htmLawed.php');
        $spec = false;
        if (isset($config['spec']))
            $spec = $config['spec'];

        // Add in htmLawed defaults
        $config += array(
            'balance' => 1,
        );

        // Attempt to balance using libxml. htmLawed will corrupt HTML with
        // balancing to fix improper HTML at the same time. For instance,
        // some email clients may wrap block elements inside inline
        // elements. htmLawed will change such block elements to inlines to
        // make the HTML correct.
        if ($config['balance'] && extension_loaded('dom')) {
            $html = self::html_balance($html);
            $config['balance'] = 0;
        }

        return htmLawed($html, $config, $spec);
    }

    function html2text($html, $width=74, $tidy=true) {


        # Tidy html: decode, balance, sanitize tags
        if($tidy)
            $html = Format::html(Format::htmldecode($html), array('balance' => 1));

        # See if advanced html2text is available (requires xml extension)
        if (function_exists('convert_html_to_text')
                && extension_loaded('dom'))
            return convert_html_to_text($html, $width);

        # Try simple html2text  - insert line breaks after new line tags.
        $html = preg_replace(
                array(':<br ?/?\>:i', ':(</div>)\s*:i', ':(</p>)\s*:i'),
                array("\n", "$1\n", "$1\n\n"),
                $html);

        # Strip tags, decode html chars and wrap resulting text.
        return Format::wrap(
                Format::htmldecode( Format::striptags($html, false)),
                $width);
    }

    static function __html_cleanup($el, $attributes=0) {
        static $eE = array('area'=>1, 'br'=>1, 'col'=>1, 'embed'=>1,
            'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'param'=>1);

        // We're dealing with closing tag
        if ($attributes === 0)
            return "</{$el}>";

        // Remove iframe and embed without src (perhaps striped by spec)
        // It would be awesome to rickroll such entry :)
        if (in_array($el, array('iframe', 'embed'))
                && (!isset($attributes['src']) || empty($attributes['src'])))
            return '';

        // Clean unexpected class values
        if (isset($attributes['class'])) {
            $classes = explode(' ', $attributes['class']);
            foreach ($classes as $i=>$a)
                // Unset all unsupported style classes -- anything but M$
                if (strpos($a, 'Mso') !== 0)
                    unset($classes[$i]);
            if ($classes)
                $attributes['class'] = implode(' ', $classes);
            else
                unset($attributes['class']);
        }
        // Clean browser-specific style attributes
        if (isset($attributes['style'])) {
            $styles = preg_split('/;\s*/S', html_entity_decode($attributes['style']));
            $props = array();
            foreach ($styles as $i=>&$s) {
                @list($prop, $val) = explode(':', $s);
                if (isset($props[$prop])) {
                    unset($styles[$i]);
                    continue;
                }
                $props[$prop] = true;
                // Remove unset or browser-specific style rules
                if (!$val || !$prop || $prop[0] == '-' || substr($prop, 0, 4) == 'mso-')
                    unset($styles[$i]);
                // Remove quotes of properties without enclosed space
                if (!strpos($val, ' '))
                    $val = str_replace('"','', $val);
                else
                    $val = str_replace('"',"'", $val);
                $s = "$prop:".trim($val);
            }
            unset($s);
            if ($styles)
                $attributes['style'] = Format::htmlchars(implode(';', $styles));
            else
                unset($attributes['style']);
        }
        $at = '';
        if (is_array($attributes)) {
            foreach ($attributes as $k=>$v)
                $at .= " $k=\"$v\"";
            return "<{$el}{$at}".(isset($eE[$el])?" /":"").">";
        }
        else {
            return "</{$el}>";
        }
    }

    function safe_html($html, $options=array()) {

        $options = array_merge(array(
                    // Balance html tags
                    'balance' => 1,
                    // Decoding special html char like &lt; and &gt; which
                    // can be used to skip cleaning
                    'decode' => true
                    ),
                $options);

        if ($options['decode'])
            $html = Format::htmldecode($html);

        // Remove HEAD and STYLE sections
        $html = preg_replace(
            array(':<(head|style|script).+?</\1>:is', # <head> and <style> sections
                  ':<!\[[^]<]+\]>:',            # <![if !mso]> and friends
                  ':<!DOCTYPE[^>]+>:',          # <!DOCTYPE ... >
                  ':<\?[^>]+>:',                # <?xml version="1.0" ... >
            ),
            array('', '', '', ''),
            $html);

        // HtmLawed specific config only
        $config = array(
            'safe' => 1, //Exclude applet, embed, iframe, object and script tags.
            'balance' => $options['balance'],
            'comment' => 1, //Remove html comments (OUTLOOK LOVE THEM)
            'tidy' => -1,
            'deny_attribute' => 'id',
            'schemes' => 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; *:file, http, https; src: cid, http, https, data',
            'hook_tag' => function($e, $a=0) { return Format::__html_cleanup($e, $a); },
            'elements' => '*+iframe',
            'spec' => 'iframe=-*,height,width,type,src(match="`^(https?:)?//(www\.)?(youtube|dailymotion|vimeo)\.com/`i"),frameborder',
        );

        return Format::html($html, $config);
    }

    function localizeInlineImages($text) {
        // Change file.php urls back to content-id's
        return preg_replace(
            '`src="(?:https?:/)?(?:/[^/"]+)*?/file\\.php\\?(?:\w+=[^&]+&(?:amp;)?)*?key=([^&]+)[^"]*`',
            'src="cid:$1', $text);
    }

    function sanitize($text, $striptags=false) {

        //balance and neutralize unsafe tags.
        $text = Format::safe_html($text);

        $text = self::localizeInlineImages($text);

        //If requested - strip tags with decoding disabled.
        return $striptags?Format::striptags($text, false):$text;
    }

    function htmlchars($var, $sanitize = false) {
        static $phpversion = null;

        if (is_array($var))
            return array_map(array('Format', 'htmlchars'), $var);

        if ($sanitize)
            $var = Format::sanitize($var);

        if (!isset($phpversion))
            $phpversion = phpversion();

        $flags = ENT_COMPAT;
        if ($phpversion >= '5.4.0')
            $flags |= ENT_HTML401;

        try {
            return htmlspecialchars( (string) $var, $flags, 'UTF-8', false);
        } catch(Exception $e) {
            return $var;
        }
    }

    function htmldecode($var) {

        if(is_array($var))
            return array_map(array('Format','htmldecode'), $var);

        $flags = ENT_COMPAT;
        if (phpversion() >= '5.4.0')
            $flags |= ENT_HTML401;

        return htmlspecialchars_decode($var, $flags);
    }

    function input($var) {
        return Format::htmlchars($var);
    }

    //Format text for display..
    function display($text, $inline_images=true) {
        // Make showing offsite images optional
        $text = preg_replace_callback('/<img ([^>]*)(src="http[^"]+")([^>]*)\/>/',
            function($match) {
                // Drop embedded classes -- they don't refer to ours
                $match = preg_replace('/class="[^"]*"/', '', $match);
                return sprintf('<span %s class="non-local-image" data-%s %s></span>',
                    $match[1], $match[2], $match[3]);
            },
            $text);

        //make urls clickable.
        $text = self::html_balance($text, false);
        $text = Format::clickableurls($text);

        if ($inline_images)
            return self::viewableImages($text);

        return $text;
    }

    function striptags($var, $decode=true) {

        if(is_array($var))
            return array_map(array('Format','striptags'), $var, array_fill(0, count($var), $decode));

        return strip_tags($decode?Format::htmldecode($var):$var);
    }

    //make urls clickable. Mainly for display
    function clickableurls($text, $target='_blank') {
        global $ost;

        // Find all text between tags
        $text = preg_replace_callback(':^[^<]+|>[^<]+:',
            function($match) {
                // Scan for things that look like URLs
                return preg_replace_callback(
                    '`(?<!>)(((f|ht)tp(s?)://|(?<!//)www\.)([-+~%/.\w]+)(?:[-?#+=&;%@.\w]*)?)'
                   .'|(\b[_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,4})`',
                    function ($match) {
                        if ($match[1]) {
                            while (in_array(substr($match[1], -1),
                                    array('.','?','-',':',';'))) {
                                $match[9] = substr($match[1], -1) . $match[9];
                                $match[1] = substr($match[1], 0, strlen($match[1])-1);
                            }
                            if (strpos($match[2], '//') === false) {
                                $match[1] = 'http://' . $match[1];
                            }

                            return sprintf('<a href="%s">%s</a>%s',
                                $match[1], $match[1], $match[9]);
                        } elseif ($match[6]) {
                            return sprintf('<a href="mailto:%1$s" target="_blank">%1$s</a>',
                                $match[6]);
                        }
                    },
                    $match[0]);
            },
            $text);

        // Now change @href and @src attributes to come back through our
        // system as well
        $config = array(
            'hook_tag' => function($e, $a=0) use ($target) {
                static $eE = array('area'=>1, 'br'=>1, 'col'=>1, 'embed'=>1,
                    'hr'=>1, 'img'=>1, 'input'=>1, 'isindex'=>1, 'param'=>1);
                if ($e == 'a' && $a) {
                    $a['target'] = $target;
                    $a['class'] = 'no-pjax';
                }

                $at = '';
                if (is_array($a)) {
                    foreach ($a as $k=>$v)
                        $at .= " $k=\"$v\"";
                    return "<{$e}{$at}".(isset($eE[$e])?" /":"").">";
                } else {
                    return "</{$e}>";
                }
            },
            'schemes' => 'href: aim, feed, file, ftp, gopher, http, https, irc, mailto, news, nntp, sftp, ssh, telnet; *:file, http, https; src: cid, http, https, data',
            'elements' => '*+iframe',
            'balance' => 0,
            'spec' => 'span=data-src,width,height',
        );
        return Format::html($text, $config);
    }

    function stripEmptyLines($string) {
        return preg_replace("/\n{3,}/", "\n\n", trim($string));
    }


    function viewableImages($html, $script=false) {
        return preg_replace_callback('/"cid:([\w._-]{32})"/',
        function($match) use ($script) {
            $hash = $match[1];
            if (!($file = AttachmentFile::lookup($hash)))
                return $match[0];
            return sprintf('"%s" data-cid="%s"',
                $file->getDownloadUrl(false, 'inline', $script), $match[1]);
        }, $html);
    }


    /**
     * Thanks, http://us2.php.net/manual/en/function.implode.php
     * Implode an array with the key and value pair giving
     * a glue, a separator between pairs and the array
     * to implode.
     * @param string $glue The glue between key and value
     * @param string $separator Separator between pairs
     * @param array $array The array to implode
     * @return string The imploded array
    */
    function array_implode( $glue, $separator, $array ) {

        if ( !is_array( $array ) ) return $array;

        $string = array();
        foreach ( $array as $key => $val ) {
            if ( is_array( $val ) )
                $val = implode( ',', $val );

            $string[] = "{$key}{$glue}{$val}";
        }

        return implode( $separator, $string );
    }

    /* elapsed time */
    function elapsedTime($sec) {

        if(!$sec || !is_numeric($sec)) return "";

        $days = floor($sec / 86400);
        $hrs = floor(bcmod($sec,86400)/3600);
        $mins = round(bcmod(bcmod($sec,86400),3600)/60);
        if($days > 0) $tstring = $days . 'd,';
        if($hrs > 0) $tstring = $tstring . $hrs . 'h,';
        $tstring =$tstring . $mins . 'm';

        return $tstring;
    }

    /* Dates helpers...most of this crap will change once we move to PHP 5*/
    function db_date($time) {
        global $cfg;
        return Format::userdate($cfg->getDateFormat(), Misc::db2gmtime($time));
    }

    function db_datetime($time) {
        global $cfg;
        return Format::userdate($cfg->getDateTimeFormat(), Misc::db2gmtime($time));
    }

    function db_daydatetime($time) {
        global $cfg;
        return Format::userdate($cfg->getDayDateTimeFormat(), Misc::db2gmtime($time));
    }

    function userdate($format, $gmtime) {
        return Format::date($format, $gmtime, $_SESSION['TZ_OFFSET'], $_SESSION['TZ_DST']);
    }

    function date($format, $gmtimestamp, $offset=0, $daylight=false){

        if(!$gmtimestamp || !is_numeric($gmtimestamp))
            return "";

        $offset+=$daylight?date('I', $gmtimestamp):0; //Daylight savings crap.

        return date($format, ($gmtimestamp+ ($offset*3600)));
    }

    // Thanks, http://stackoverflow.com/a/2955878/1025836
    /* static */
    function slugify($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\p{L}\p{N}]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // lowercase
        $text = strtolower($text);

        return (empty($text)) ? 'n-a' : $text;
    }

    /**
     * Parse RFC 2397 formatted data strings. Format according to the RFC
     * should look something like:
     *
     * data:[type/subtype][;charset=utf-8][;base64],data
     *
     * Parameters:
     * $data - (string) RFC2397 formatted data string
     * $output_encoding - (string:optional) Character set the input data
     *      should be encoded to.
     * $always_convert - (bool|default:true) If the input data string does
     *      not specify an input encding, assume iso-8859-1. If this flag is
     *      set, the output will always be transcoded to the declared
     *      output_encoding, if set.
     *
     * Returs:
     * array (data=>parsed and transcoded data string, type=>MIME type
     * declared in the data string or text/plain otherwise)
     *
     * References:
     * http://www.ietf.org/rfc/rfc2397.txt
     */
    function parseRfc2397($data, $output_encoding=false, $always_convert=true) {
        if (substr($data, 0, 5) != "data:")
            return array('data'=>$data, 'type'=>'text/plain');

        $data = substr($data, 5);
        list($meta, $contents) = explode(",", $data, 2);
        if ($meta)
            list($type, $extra) = explode(";", $meta, 2);
        else
            $extra = '';
        if (!isset($type) || !$type)
            $type = 'text/plain';

        $parameters = explode(";", $extra);

        # Handle 'charset' hint in $extra, such as
        # data:text/plain;charset=iso-8859-1,Blah
        # Convert to utf-8 since it's the encoding scheme for the database.
        $charset = ($always_convert) ? 'iso-8859-1' : false;
        foreach ($parameters as $p) {
            list($param, $value) = explode('=', $extra);
            if ($param == 'charset')
                $charset = $value;
            elseif ($param == 'base64')
                $contents = base64_decode($contents);
        }
        if ($output_encoding && $charset)
            $contents = Charset::transcode($contents, $charset, $output_encoding);

        return array(
            'data' => $contents,
            'type' => $type
        );
    }

    // Performs Unicode normalization (where possible) and splits words at
    // difficult word boundaries (for far eastern languages)
    function searchable($text, $lang=false) {
        global $cfg;

        if (function_exists('normalizer_normalize')) {
            // Normalize text input :: remove diacritics and such
            $text = normalizer_normalize($text, Normalizer::FORM_C);
        }
        else {
            // As a lightweight compatiblity, use a lightweight C
            // normalizer with diacritic removal, thanks
            // http://ahinea.com/en/tech/accented-translate.html
            $tr = array(
                "ä" => "a", "ñ" => "n", "ö" => "o", "ü" => "u", "ÿ" => "y"
            );
            $text = strtr($text, $tr);
        }
        // Decompose compatible versions of characters (ä => ae)
        $tr = array(
            "ß" => "ss", "Æ" => "AE", "æ" => "ae", "Ĳ" => "IJ",
            "ĳ" => "ij", "Œ" => "OE", "œ" => "oe", "Ð" => "D",
            "Đ" => "D", "ð" => "d", "đ" => "d", "Ħ" => "H", "ħ" => "h",
            "ı" => "i", "ĸ" => "k", "Ŀ" => "L", "Ł" => "L", "ŀ" => "l",
            "ł" => "l", "Ŋ" => "N", "ŉ" => "n", "ŋ" => "n", "Ø" => "O",
            "ø" => "o", "ſ" => "s", "Þ" => "T", "Ŧ" => "T", "þ" => "t",
            "ŧ" => "t", "ä" => "ae", "ö" => "oe", "ü" => "ue",
            "Ä" => "AE", "Ö" => "OE", "Ü" => "UE",
        );
        $text = strtr($text, $tr);

        // Drop separated diacritics
        $text = preg_replace('/\p{M}/u', '', $text);

        // Drop extraneous whitespace
        $text = preg_replace('/(\s)\s+/u', '$1', $text);

        // Drop leading and trailing whitespace
        $text = trim($text);

        if (false && class_exists('IntlBreakIterator')) {
            // Split by word boundaries
            if ($tokenizer = IntlBreakIterator::createWordInstance(
                    $lang ?: ($cfg ? $cfg->getSystemLanguage() : 'pt_BR'))
            ) {
                $tokenizer->setText($text);
                $tokens = array();
                foreach ($tokenizer as $token)
                    $tokens[] = $token;
                $text = implode(' ', $tokens);
            }
        }
        else {
            // Approximate word boundaries from Unicode chart at
            // http://www.unicode.org/reports/tr29/#Word_Boundaries

            // Punt for now
        }
        return $text;
    }
}
?>
