<?php
if (!class_exists('Texy', FALSE)) die();
class TexyHtmlModule extends TexyModule
{
    protected $default = array(
        'html/tag' => TRUE,
        'html/comment' => TRUE,
    );
    public $passComment = TRUE;
    public function begin()
    {
        $this->texy->registerLinePattern(
            array($this, 'patternTag'),
            '#<(/?)([a-z][a-z0-9_:-]*)((?:\s+[a-z0-9:-]+|=\s*"[^"'.TEXY_MARK.']*"|=\s*\'[^\''.TEXY_MARK.']*\'|=[^\s>'.TEXY_MARK.']+)*)\s*(/?)>#isu',
            'html/tag'
        );
        $this->texy->registerLinePattern(
            array($this, 'patternComment'),
            '#<!--([^'.TEXY_MARK.']*?)-->#is',
            'html/comment'
        );
    }
    public function patternComment($parser, $matches)
    {
        list(, $mComment) = $matches;
        if (is_callable(array($this->texy->handler, 'htmlComment'))) {
            $res = $this->texy->handler->htmlComment($parser, $mComment);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solveComment($mComment);
    }
    public function patternTag($parser, $matches)
    {
        list(, $mEnd, $mTag, $mAttr, $mEmpty) = $matches;
        $tx = $this->texy;
        $isStart = $mEnd !== '/';
        $isEmpty = $mEmpty === '/';
        if (!$isEmpty && substr($mAttr, -1) === '/') { // uvizlo v $mAttr?
            $mAttr = substr($mAttr, 0, -1);
            $isEmpty = TRUE;
        }
        if ($isEmpty && !$isStart)
            return FALSE;
        $mAttr = trim(strtr($mAttr, "\n", ' '));
        if ($mAttr && !$isStart)
            return FALSE;
        $el = TexyHtml::el($mTag);
        if (!$isStart) {
            if (is_callable(array($tx->handler, 'htmlTag'))) {
                $res = $tx->handler->htmlTag($parser, $el, FALSE);
                if ($res !== Texy::PROCEED) return $res;
            }
            return $this->solveTag($el, FALSE);
        }
        $matches2 = NULL;
        preg_match_all(
            '#([a-z0-9:-]+)\s*(?:=\s*(\'[^\']*\'|"[^"]*"|[^\'"\s]+))?()#isu',
            $mAttr,
            $matches2,
            PREG_SET_ORDER
        );
        foreach ($matches2 as $m) {
            $key = strtolower($m[1]);
            $val = $m[2];
            if ($val == NULL) $el->attrs[$key] = TRUE;
            elseif ($val{0} === '\'' || $val{0} === '"') $el->attrs[$key] = Texy::unescapeHtml(substr($val, 1, -1));
            else $el->attrs[$key] = Texy::unescapeHtml($val);
        }
        if (is_callable(array($tx->handler, 'htmlTag'))) {
            $res = $tx->handler->htmlTag($parser, $el, TRUE, $isEmpty);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solveTag($el, TRUE, $isEmpty);
    }
    public function solveTag(TexyHtml $el, $isStart, $forceEmpty=NULL)
    {
        $tx = $this->texy;
        $aTags = $tx->allowedTags; // speed-up
        if (!$aTags) {
            return FALSE;  // all tags are disabled
        } elseif (is_array($aTags)) {
            if (!isset($aTags[$el->name])) {
                $el->setName(strtolower($el->name));
                if (!isset($aTags[$el->name])) return FALSE; // this element not allowed
            }
            $aAttrs = $aTags[$el->name]; // allowed attrs
        } else { // allowedTags === Texy::ALL
            if ($el->name === strtoupper($el->name))
                $el->setName(strtolower($el->name));
            $aAttrs = Texy::ALL; // all attrs are allowed
        }
        if ($forceEmpty && $aTags === Texy::ALL) $el->isEmpty = TRUE;
        if (!$isStart) {
            return $tx->protect($el->endTag(), $el->getContentType());
        }
        $elAttrs = & $el->attrs;
        if (!$aAttrs) {
            $elAttrs = array();
        } elseif (is_array($aAttrs)) {
            $aAttrs = array_flip($aAttrs);
            foreach ($elAttrs as $key => $foo)
                if (!isset($aAttrs[$key])) unset($elAttrs[$key]);
        }
        $tmp = $tx->_classes; // speed-up
        if (isset($elAttrs['class'])) {
            if (is_array($tmp)) {
                $elAttrs['class'] = explode(' ', $elAttrs['class']);
                foreach ($elAttrs['class'] as $key => $val)
                    if (!isset($tmp[$val])) unset($elAttrs['class'][$key]); // id & class are case-sensitive in XHTML
            } elseif ($tmp !== Texy::ALL) {
                $elAttrs['class'] = NULL;
            }
        }
        if (isset($elAttrs['id'])) {
            if (is_array($tmp)) {
                if (!isset($tmp['#' . $elAttrs['id']])) $elAttrs['id'] = NULL;
            } elseif ($tmp !== Texy::ALL) {
                $elAttrs['id'] = NULL;
            }
        }
        if (isset($elAttrs['style'])) {
            $tmp = $tx->_styles;  // speed-up
            if (is_array($tmp)) {
                $styles = explode(';', $elAttrs['style']);
                $elAttrs['style'] = NULL;
                foreach ($styles as $value) {
                    $pair = explode(':', $value, 2);
                    $prop = trim($pair[0]);
                    if (isset($pair[1]) && isset($tmp[strtolower($prop)])) // CSS is case-insensitive
                        $elAttrs['style'][$prop] = $pair[1];
                }
            } elseif ($tmp !== Texy::ALL) {
                $elAttrs['style'] = NULL;
            }
        }
        if ($el->name === 'img') {
            if (!isset($elAttrs['src'])) return FALSE;
            if (!$tx->checkURL($elAttrs['src'], 'i')) return FALSE;
            $tx->summary['images'][] = $elAttrs['src'];
        } elseif ($el->name === 'a') {
            if (!isset($elAttrs['href']) && !isset($elAttrs['name']) && !isset($elAttrs['id'])) return FALSE;
            if (isset($elAttrs['href'])) {
                if ($tx->linkModule->forceNoFollow && strpos($elAttrs['href'], '//') !== FALSE) {
                    if (isset($elAttrs['rel'])) $elAttrs['rel'] = (array) $elAttrs['rel'];
                    $elAttrs['rel'][] = 'nofollow';
                }
                if (!$tx->checkURL($elAttrs['href'], 'a')) return FALSE;
                $tx->summary['links'][] = $elAttrs['href'];
            }
        }
        return $tx->protect($el->startTag(), $el->getContentType());
    }
    public function solveComment($content)
    {
        if (!$this->passComment) return '';
        $content = preg_replace('#-{2,}#', '-', $content);
        $content = rtrim($content, '-');
        return $this->texy->protect('<!--' . $content . '-->', Texy::CONTENT_MARKUP);
    }
} // TexyHtmlModule
