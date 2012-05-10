<?php
if (!class_exists('Texy', FALSE)) die();
class TexyParagraphModule extends TexyModule
{
    public $mode;
    public function begin()
    {
        $this->mode = TRUE;
    }
    public function solve($content, $mod)
    {
        $tx = $this->texy;
        if ($tx->mergeLines) {
            $content = preg_replace('#\n (?=\S)#', "\r", $content);
        } else {
            $content = preg_replace('#\n#', "\r", $content);
        }
        $el = TexyHtml::el('p');
        $el->parseLine($tx, $content);
        $content = $el->getText(); // string
        if (strpos($content, Texy::CONTENT_BLOCK) !== FALSE) {
            $el->name = '';  // ignores modifier!
        } elseif (strpos($content, Texy::CONTENT_TEXTUAL) !== FALSE) {
        } elseif (preg_match('#[^\s'.TEXY_MARK.']#u', $content)) {
        } elseif (strpos($content, Texy::CONTENT_REPLACED) !== FALSE) {
            $el->name = 'div';
        } else {
            if ($tx->ignoreEmptyStuff) return FALSE;
            if ($mod->empty) $el->name = '';
        }
        if ($el->name && $mod) $mod->decorate($tx, $el);
        if ($el->name && (strpos($content, "\r") !== FALSE)) {
            $key = $tx->protect('<br />', Texy::CONTENT_REPLACED);
            $content = str_replace("\r", $key, $content);
        };
        $content = strtr($content, "\r\n", '  ');
        $el->setText($content);
        return $el;
    }
} // TexyParagraphModule
