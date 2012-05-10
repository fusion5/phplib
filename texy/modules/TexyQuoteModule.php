<?php
if (!class_exists('Texy', FALSE)) die();
class TexyQuoteModule extends TexyModule
{
    protected $default = array('blockquote' => TRUE);
    public function begin()
    {
        $this->texy->registerBlockPattern(
            array($this, 'pattern'),
            '#^(?:'.TEXY_MODIFIER_H.'\n)?\>(\ +|:)(\S.*)$#mU', // original
            'blockquote'
        );
    }
    public function pattern($parser, $matches)
    {
        list(, $mMod, $mPrefix, $mContent) = $matches;
        $tx = $this->texy;
        $el = TexyHtml::el('blockquote');
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $el);
        $content = '';
        $spaces = '';
        do {
            if ($mPrefix === ':') {
                $mod->cite = $tx->quoteModule->citeLink($mContent);
                $content .= "\n";
            } else {
                if ($spaces === '') $spaces = max(1, strlen($mPrefix));
                $content .= $mContent . "\n";
            }
            if (!$parser->next("#^>(?:|(\\ {1,$spaces}|:)(.*))()$#mA", $matches)) break;
            list(, $mPrefix, $mContent) = $matches;
        } while (TRUE);
        $el->attrs['cite'] = $mod->cite;
        $el->parseBlock($tx, $content);
        if (!count($el->children)) return FALSE;
        if (is_callable(array($tx->handler, 'afterBlockquote')))
            $tx->handler->afterBlockquote($parser, $el, $mod);
        return $el;
    }
    public function citeLink($link)
    {
        $tx = $this->texy;
        if ($link == NULL) return NULL;
        if ($link{0} === '[') { // [ref]
            $link = substr($link, 1, -1);
            $ref = $tx->linkModule->getReference($link);
            if ($ref) return Texy::prependRoot($ref['URL'], $tx->linkModule->root);
        }
        if (!$tx->checkURL($link, 'c')) return NULL;
        if (strncasecmp($link, 'www.', 4) === 0) return 'http://' . $link;
        return Texy::prependRoot($link, $tx->linkModule->root);
    }
} // TexyQuoteModule
