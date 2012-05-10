<?php
if (!class_exists('Texy', FALSE)) die();
class TexyHtmlCleaner
{
    public $indent = TRUE;
    public $baseIndent = 0;
    public $lineWrap = 80;
    public $removeOptional = TRUE;
    private $space;
    static public $dtd;
    static private $optional = array('colgroup'=>1,'dd'=>1,'dt'=>1,'li'=>1,'option'=>1,
        'p'=>1,'tbody'=>1,'td'=>1,'tfoot'=>1,'th'=>1,'thead'=>1,'tr'=>1);
    static private $prohibits = array(
        'a' => array('a','button'),
        'img' => array('pre'),
        'object' => array('pre'),
        'big' => array('pre'),
        'small' => array('pre'),
        'sub' => array('pre'),
        'sup' => array('pre'),
        'input' => array('button'),
        'select' => array('button'),
        'textarea' => array('button'),
        'label' => array('button', 'label'),
        'button' => array('button'),
        'form' => array('button', 'form'),
        'fieldset' => array('button'),
        'iframe' => array('button'),
        'isindex' => array('button'),
    );
    static public $block = array('ins'=>1,'del'=>1,'p'=>1,'h1'=>1,'h2'=>1,'h3'=>1,'h4'=>1,
        'h5'=>1,'h6'=>1,'ul'=>1,'ol'=>1,'dl'=>1,'pre'=>1,'div'=>1,'blockquote'=>1,'noscript'=>1,
        'noframes'=>1,'form'=>1,'hr'=>1,'table'=>1,'address'=>1,'fieldset'=>1);
    static public $_blockLoose = array(
        'dir'=>1,'menu'=>1,'center'=>1,'iframe'=>1,'isindex'=>1, // transitional
        'marquee'=>1, // proprietary
    );
    static public $inline = array('ins'=>1,'del'=>1,'tt'=>1,'i'=>1,'b'=>1,'big'=>1,'small'=>1,'em'=>1,
        'strong'=>1,'dfn'=>1,'code'=>1,'samp'=>1,'kbd'=>1,'var'=>1,'cite'=>1,'abbr'=>1,'acronym'=>1,
        'sub'=>1,'sup'=>1,'q'=>1,'span'=>1,'bdo'=>1,'a'=>1,'object'=>1,'img'=>1,'br'=>1,'script'=>1,
        'map'=>1,'input'=>1,'select'=>1,'textarea'=>1,'label'=>1,'button'=>1,'%DATA'=>1);
    static public $_inlineLoose = array(
        'u'=>1,'s'=>1,'strike'=>1,'font'=>1,'applet'=>1,'basefont'=>1, // transitional
        'embed'=>1,'wbr'=>1,'nobr'=>1,'canvas'=>1, // proprietary
    );
    private $tagUsed;
    private $tagStack;
    private $texy;
    private $baseDTD;
    public function __construct($texy)
    {
        $this->texy = $texy;
        if (!self::$dtd) self::initDTD();
        $this->baseDTD = self::$dtd['div'][1] + array('html'=>1);
    }
    public function process($s)
    {
        $this->space = $this->baseIndent;
        $this->tagStack = array();
        $this->tagUsed  = array();
        $s = preg_replace_callback(
            '#(.*)<(?:(!--.*--)|(/?)([a-z][a-z0-9._:-]*)(|[ \n].*)\s*(/?))>()#Uis',
            array($this, 'cb'),
            $s . '</end/>'
        );
        foreach ($this->tagStack as $item) $s .= $item['close'];
        $s = preg_replace("#[\t ]+(\n|\r|$)#", '$1', $s); // right trim
        $s = str_replace("\r\r", "\n", $s);
        $s = strtr($s, "\r", "\n");
        $s = preg_replace("#\\x07 *#", '', $s);
        $s = preg_replace("#\\t? *\\x08#", '', $s);
        if ($this->lineWrap > 0)
            $s = preg_replace_callback(
                '#^(\t*)(.*)$#m',
                array($this, 'wrap'),
                $s
            );
        if (!TexyHtml::$xhtml && $this->removeOptional)
            $s = preg_replace('#\\s*</(colgroup|dd|dt|li|option|p|td|tfoot|th|thead|tr)>#u', '', $s);
        return $s;
    }
    private function cb($matches)
    {
        list(, $mText, $mComment, $mEnd, $mTag, $mAttr, $mEmpty) = $matches;
        $s = '';
        if ($mText !== '') {
            $item = reset($this->tagStack);
            if ($item && !isset($item['content']['%DATA'])) { }
            elseif (!empty($this->tagUsed['pre']) || !empty($this->tagUsed['textarea']))
                $s = Texy::freezeSpaces($mText);
            else $s = preg_replace('#[ \n]+#', ' ', $mText);
        }
        if ($mComment) return $s . '<' . Texy::freezeSpaces($mComment) . '>';
        $mEmpty = $mEmpty || isset(TexyHtml::$emptyTags[$mTag]);
        if ($mEmpty && $mEnd) return $s; // bad tag; /end/
        if ($mEnd) {  // end tag
            if (empty($this->tagUsed[$mTag])) return $s;
            $tmp = array();
            $back = TRUE;
            foreach ($this->tagStack as $i => $item)
            {
                $tag = $item['tag'];
                if ($item['close']) {
                    $s .= $item['close'];
                    if (!isset(self::$inline[$tag])) $this->space--;
                }
                $this->tagUsed[$tag]--;
                $back = $back && isset(self::$inline[$tag]);
                unset($this->tagStack[$i]);
                if ($tag === $mTag) break;
                array_unshift($tmp, $item);
            }
            if (!$back || !$tmp) return $s;
            $item = reset($this->tagStack);
            if ($item) $content = $item['content'];
            else $content = $this->baseDTD;
            if (!isset($content[$tmp[0]['tag']])) return $s;
            foreach ($tmp as $item)
            {
                if ($item['close']) $s .= '<'.$item['tag'].$item['attr'].'>';
                $this->tagUsed[$item['tag']]++;
                array_unshift($this->tagStack, $item);
            }
        } else { // start tag
            $content = $this->baseDTD;
            if (!isset(self::$dtd[$mTag][1])) {
                $allowed = $this->texy->allowedTags === Texy::ALL;
                $item = reset($this->tagStack);
                if ($item) $content = $item['content'];
            } else {
                foreach ($this->tagStack as $i => $item)
                {
                    $content = $item['content'];
                    if (isset($content[$mTag])) break;
                    $tag = $item['tag'];
                    if ($item['close'] && (!isset(self::$optional[$tag]) && !isset(self::$inline[$tag]))) break;
                    if ($item['close']) {
                        $s .= $item['close'];
                        if (!isset(self::$inline[$tag])) $this->space--;
                    }
                    $this->tagUsed[$tag]--;
                    unset($this->tagStack[$i]);
                    $content = $this->baseDTD;
                }
                $allowed = isset($content[$mTag]);
                if ($allowed && isset(self::$prohibits[$mTag])) {
                    foreach (self::$prohibits[$mTag] as $pTag)
                        if (!empty($this->tagUsed[$pTag])) { $allowed = FALSE; break; }
                }
            }
            if ($mEmpty) {
                if (!$allowed) return $s;
                if (TexyHtml::$xhtml) $mAttr .= " /";
                if ($this->indent && $mTag === 'br')
                    return rtrim($s) .  '<' . $mTag . $mAttr . ">\n" . str_repeat("\t", max(0, $this->space - 1)) . "\x07";
                if ($this->indent && !isset(self::$inline[$mTag])) {
                    $space = "\r" . str_repeat("\t", $this->space);
                    return $s . $space . '<' . $mTag . $mAttr . '>' . $space;
                }
                return $s . '<' . $mTag . $mAttr . '>';
            }
            if ($allowed) {
                if (!empty(self::$dtd[$mTag][1])) $content = self::$dtd[$mTag][1];
                if ($this->indent && !isset(self::$inline[$mTag])) {
                    $close = "\x08" . '</'.$mTag.'>' . "\n" . str_repeat("\t", $this->space);
                    $s .= "\n" . str_repeat("\t", $this->space++) . '<'.$mTag.$mAttr.'>' . "\x07";
                } else {
                    $close = '</'.$mTag.'>';
                    $s .= '<'.$mTag.$mAttr.'>';
                }
            } else $close = '';
            $item = array(
                'tag' => $mTag,
                'attr' => $mAttr,
                'close' => $close,
                'content' => $content,
            );
            array_unshift($this->tagStack, $item);
            $tmp = &$this->tagUsed[$mTag]; $tmp++;
        }
        return $s;
    }
    private function wrap($m)
    {
        list(, $space, $s) = $m;
        return $space . wordwrap($s, $this->lineWrap, "\n" . $space);
    }
    static public function initDTD()
    {
        $strict = Texy::$strictDTD;
        $coreattrs = array('id'=>1,'class'=>1,'style'=>1,'title'=>1,'xml:id'=>1); // extra: xml:id
        $i18n = array('lang'=>1,'dir'=>1,'xml:lang'=>1); // extra: xml:lang
        $attrs = $coreattrs + $i18n + array('onclick'=>1,'ondblclick'=>1,'onmousedown'=>1,'onmouseup'=>1,
            'onmouseover'=>1, 'onmousemove'=>1,'onmouseout'=>1,'onkeypress'=>1,'onkeydown'=>1,'onkeyup'=>1);
        $cellalign = $attrs + array('align'=>1,'char'=>1,'charoff'=>1,'valign'=>1);
        if (!$strict) self::$block += self::$_blockLoose;
        $b = self::$block;
        if (!$strict) self::$inline += self::$_inlineLoose;
        $i = self::$inline;
        $bi = $b + $i;
        self::$dtd = array(
        'html' => array(
             $strict ? $i18n + array('xmlns'=>1) : $i18n + array('version'=>1,'xmlns'=>1), // extra: xmlns
             array('head'=>1,'body'=>1),
        ),
        'head' => array(
             $i18n + array('profile'=>1),
             array('title'=>1,'script'=>1,'style'=>1,'base'=>1,'meta'=>1,'link'=>1,'object'=>1,'isindex'=>1),
        ),
        'title' => array(
             array(),
             array('%DATA'=>1),
        ),
        'body' => array(
             $attrs + array('onload'=>1,'onunload'=>1),
             $strict ? array('script'=>1) + $b : $bi,
        ),
        'script' => array(
             array('charset'=>1,'type'=>1,'src'=>1,'defer'=>1,'event'=>1,'for'=>1),
             array('%DATA'=>1),
        ),
        'style' => array(
             $i18n + array('type'=>1,'media'=>1,'title'=>1),
             array('%DATA'=>1),
        ),
        'p' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $i,
        ),
        'h1' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $i,
        ),
        'h2' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $i,
        ),
        'h3' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $i,
        ),
        'h4' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $i,
        ),
        'h5' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $i,
        ),
        'h6' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $i,
        ),
        'ul' => array(
             $strict ? $attrs : $attrs + array('type'=>1,'compact'=>1),
             array('li'=>1),
        ),
        'ol' => array(
             $strict ? $attrs : $attrs + array('type'=>1,'compact'=>1,'start'=>1),
             array('li'=>1),
        ),
        'li' => array(
             $strict ? $attrs : $attrs + array('type'=>1,'value'=>1),
             $bi,
        ),
        'dl' => array(
             $strict ? $attrs : $attrs + array('compact'=>1),
             array('dt'=>1,'dd'=>1),
        ),
        'dt' => array(
             $attrs,
             $i,
        ),
        'dd' => array(
             $attrs,
             $bi,
        ),
        'pre' => array(
             $strict ? $attrs : $attrs + array('width'=>1),
             array_flip(array_diff(array_keys($i), array('img','object','applet','big','small','sub','sup','font','basefont'))),
        ),
        'div' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $bi,
        ),
        'blockquote' => array(
             $attrs + array('cite'=>1),
             $strict ? array('script'=>1) + $b : $bi,
        ),
        'noscript' => array(
             $attrs,
             $bi,
        ),
        'form' => array(
             $attrs + array('action'=>1,'method'=>1,'enctype'=>1,'accept'=>1,'name'=>1,'onsubmit'=>1,'onreset'=>1,'accept-charset'=>1),
             $strict ? array('script'=>1) + $b : $bi,
        ),
        'table' => array(
             $attrs + array('summary'=>1,'width'=>1,'border'=>1,'frame'=>1,'rules'=>1,'cellspacing'=>1,'cellpadding'=>1,'datapagesize'=>1),
             array('caption'=>1,'colgroup'=>1,'col'=>1,'thead'=>1,'tbody'=>1,'tfoot'=>1,'tr'=>1),
        ),
        'caption' => array(
             $strict ? $attrs : $attrs + array('align'=>1),
             $i,
        ),
        'colgroup' => array(
             $cellalign + array('span'=>1,'width'=>1),
             array('col'=>1),
        ),
        'thead' => array(
             $cellalign,
             array('tr'=>1),
        ),
        'tbody' => array(
             $cellalign,
             array('tr'=>1),
        ),
        'tfoot' => array(
             $cellalign,
             array('tr'=>1),
        ),
        'tr' => array(
             $strict ? $cellalign : $cellalign + array('bgcolor'=>1),
             array('td'=>1,'th'=>1),
        ),
        'td' => array(
             $cellalign + array('abbr'=>1,'axis'=>1,'headers'=>1,'scope'=>1,'rowspan'=>1,'colspan'=>1),
             $bi,
        ),
        'th' => array(
             $cellalign + array('abbr'=>1,'axis'=>1,'headers'=>1,'scope'=>1,'rowspan'=>1,'colspan'=>1),
             $bi,
        ),
        'address' => array(
             $attrs,
             $strict ? $i : array('p'=>1) + $i,
        ),
        'fieldset' => array(
             $attrs,
             array('legend'=>1) + $bi,
        ),
        'legend' => array(
             $strict ? $attrs + array('accesskey'=>1) : $attrs + array('accesskey'=>1,'align'=>1),
             $i,
        ),
        'tt' => array(
             $attrs,
             $i,
        ),
        'i' => array(
             $attrs,
             $i,
        ),
        'b' => array(
             $attrs,
             $i,
        ),
        'big' => array(
             $attrs,
             $i,
        ),
        'small' => array(
             $attrs,
             $i,
        ),
        'em' => array(
             $attrs,
             $i,
        ),
        'strong' => array(
             $attrs,
             $i,
        ),
        'dfn' => array(
             $attrs,
             $i,
        ),
        'code' => array(
             $attrs,
             $i,
        ),
        'samp' => array(
             $attrs,
             $i,
        ),
        'kbd' => array(
             $attrs,
             $i,
        ),
        'var' => array(
             $attrs,
             $i,
        ),
        'cite' => array(
             $attrs,
             $i,
        ),
        'abbr' => array(
             $attrs,
             $i,
        ),
        'acronym' => array(
             $attrs,
             $i,
        ),
        'sub' => array(
             $attrs,
             $i,
        ),
        'sup' => array(
             $attrs,
             $i,
        ),
        'q' => array(
             $attrs + array('cite'=>1),
             $i,
        ),
        'span' => array(
             $attrs,
             $i,
        ),
        'bdo' => array(
             $coreattrs + array('lang'=>1,'dir'=>1),
             $i,
        ),
        'a' => array(
             $attrs + array('charset'=>1,'type'=>1,'name'=>1,'href'=>1,'hreflang'=>1,'rel'=>1,'rev'=>1,'accesskey'=>1,'shape'=>1,'coords'=>1,'tabindex'=>1,'onfocus'=>1,'onblur'=>1),
             $i,
        ),
        'object' => array(
             $attrs + array('declare'=>1,'classid'=>1,'codebase'=>1,'data'=>1,'type'=>1,'codetype'=>1,'archive'=>1,'standby'=>1,'height'=>1,'width'=>1,'usemap'=>1,'name'=>1,'tabindex'=>1),
             array('param'=>1) + $bi,
        ),
        'map' => array(
             $attrs + array('name'=>1),
             array('area'=>1) + $b,
        ),
        'select' => array(
             $attrs + array('name'=>1,'size'=>1,'multiple'=>1,'disabled'=>1,'tabindex'=>1,'onfocus'=>1,'onblur'=>1,'onchange'=>1),
             array('option'=>1,'optgroup'=>1),
        ),
        'optgroup' => array(
             $attrs + array('disabled'=>1,'label'=>1),
             array('option'=>1),
        ),
        'option' => array(
             $attrs + array('selected'=>1,'disabled'=>1,'label'=>1,'value'=>1),
             array('%DATA'=>1),
        ),
        'textarea' => array(
             $attrs + array('name'=>1,'rows'=>1,'cols'=>1,'disabled'=>1,'readonly'=>1,'tabindex'=>1,'accesskey'=>1,'onfocus'=>1,'onblur'=>1,'onselect'=>1,'onchange'=>1),
             array('%DATA'=>1),
        ),
        'label' => array(
             $attrs + array('for'=>1,'accesskey'=>1,'onfocus'=>1,'onblur'=>1),
             $i, // - label by self::$prohibits
        ),
        'button' => array(
             $attrs + array('name'=>1,'value'=>1,'type'=>1,'disabled'=>1,'tabindex'=>1,'accesskey'=>1,'onfocus'=>1,'onblur'=>1),
             $bi, // - a input select textarea label button form fieldset, by self::$prohibits
        ),
        'ins' => array(
             $attrs + array('cite'=>1,'datetime'=>1),
             0, // special case
        ),
        'del' => array(
             $attrs + array('cite'=>1,'datetime'=>1),
             0, // special case
        ),
        'img' => array(
             $attrs + array('src'=>1,'alt'=>1,'longdesc'=>1,'name'=>1,'height'=>1,'width'=>1,'usemap'=>1,'ismap'=>1),
             FALSE,
        ),
        'hr' => array(
             $strict ? $attrs : $attrs + array('align'=>1,'noshade'=>1,'size'=>1,'width'=>1),
             FALSE,
        ),
        'br' => array(
             $strict ? $coreattrs : $coreattrs + array('clear'=>1),
             FALSE,
        ),
        'input' => array(
             $attrs + array('type'=>1,'name'=>1,'value'=>1,'checked'=>1,'disabled'=>1,'readonly'=>1,'size'=>1,'maxlength'=>1,'src'=>1,'alt'=>1,'usemap'=>1,'ismap'=>1,'tabindex'=>1,'accesskey'=>1,'onfocus'=>1,'onblur'=>1,'onselect'=>1,'onchange'=>1,'accept'=>1),
             FALSE,
        ),
        'meta' => array(
             $i18n + array('http-equiv'=>1,'name'=>1,'content'=>1,'scheme'=>1),
             FALSE,
        ),
        'area' => array(
             $attrs + array('shape'=>1,'coords'=>1,'href'=>1,'nohref'=>1,'alt'=>1,'tabindex'=>1,'accesskey'=>1,'onfocus'=>1,'onblur'=>1),
             FALSE,
        ),
        'base' => array(
             $strict ? array('href'=>1) : array('href'=>1,'target'=>1),
             FALSE,
        ),
        'col' => array(
             $cellalign + array('span'=>1,'width'=>1),
             FALSE,
        ),
        'link' => array(
             $attrs + array('charset'=>1,'href'=>1,'hreflang'=>1,'type'=>1,'rel'=>1,'rev'=>1,'media'=>1),
             FALSE,
        ),
        'param' => array(
             array('id'=>1,'name'=>1,'value'=>1,'valuetype'=>1,'type'=>1),
             FALSE,
        ),
        );
        if ($strict) return;
        self::$dtd += array(
        'dir' => array(
             $attrs + array('compact'=>1),
             array('li'=>1),
        ),
        'menu' => array(
             $attrs + array('compact'=>1),
             array('li'=>1), // it's inline-li, ignored
        ),
        'center' => array(
             $attrs,
             $bi,
        ),
        'iframe' => array(
             $coreattrs + array('longdesc'=>1,'name'=>1,'src'=>1,'frameborder'=>1,'marginwidth'=>1,'marginheight'=>1,'scrolling'=>1,'align'=>1,'height'=>1,'width'=>1),
             $bi,
        ),
        'noframes' => array(
             $attrs,
             $bi,
        ),
        'u' => array(
             $attrs,
             $i,
        ),
        's' => array(
             $attrs,
             $i,
        ),
        'strike' => array(
             $attrs,
             $i,
        ),
        'font' => array(
             $coreattrs + $i18n + array('size'=>1,'color'=>1,'face'=>1),
             $i,
        ),
        'applet' => array(
             $coreattrs + array('codebase'=>1,'archive'=>1,'code'=>1,'object'=>1,'alt'=>1,'name'=>1,'width'=>1,'height'=>1,'align'=>1,'hspace'=>1,'vspace'=>1),
             array('param'=>1) + $bi,
        ),
        'basefont' => array(
             array('id'=>1,'size'=>1,'color'=>1,'face'=>1),
             FALSE,
        ),
        'isindex' => array(
             $coreattrs + $i18n + array('prompt'=>1),
             FALSE,
        ),
        'marquee' => array(
             Texy::ALL,
             $bi,
        ),
        'nobr' => array(
             array(),
             $i,
        ),
        'canvas' => array(
             Texy::ALL,
             $i,
        ),
        'embed' => array(
             Texy::ALL,
             FALSE,
        ),
        'wbr' => array(
             array(),
             FALSE,
        ),
        );
        self::$dtd['a'][0] += array('target'=>1);
        self::$dtd['area'][0] += array('target'=>1);
        self::$dtd['body'][0] += array('background'=>1,'bgcolor'=>1,'text'=>1,'link'=>1,'vlink'=>1,'alink'=>1);
        self::$dtd['form'][0] += array('target'=>1);
        self::$dtd['img'][0] += array('align'=>1,'border'=>1,'hspace'=>1,'vspace'=>1);
        self::$dtd['input'][0] += array('align'=>1);
        self::$dtd['link'][0] += array('target'=>1);
        self::$dtd['object'][0] += array('align'=>1,'border'=>1,'hspace'=>1,'vspace'=>1);
        self::$dtd['script'][0] += array('language'=>1);
        self::$dtd['table'][0] += array('align'=>1,'bgcolor'=>1);
        self::$dtd['td'][0] += array('nowrap'=>1,'bgcolor'=>1,'width'=>1,'height'=>1);
        self::$dtd['th'][0] += array('nowrap'=>1,'bgcolor'=>1,'width'=>1,'height'=>1);
    }
    function __get($nm) { throw new Exception("Undefined property '" . get_class($this) . "::$$nm'"); }
    function __set($nm, $val) { $this->__get($nm); }
}
