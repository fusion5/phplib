<?php
if (!class_exists('Texy', FALSE)) die();
class TexyListModule extends TexyModule
{
    protected $default = array('list' => TRUE, 'list/definition' => TRUE);
    public $bullets = array(
        '*'  => array('\*\ ',               0, ''),
        '-'  => array('[\x{2013}-](?![>-])',0, ''),
        '+'  => array('\+\ ',               0, ''),
        '1.' => array('1\.\ ',   1, '',             '\d{1,3}\.\ '),
        '1)' => array('\d{1,3}\)\ ',        1, ''),
        'I.' => array('I\.\ ',              1, 'upper-roman',  '[IVX]{1,4}\.\ '),
        'I)' => array('[IVX]+\)\ ',         1, 'upper-roman'), // before A) !
        'a)' => array('[a-z]\)\ ',          1, 'lower-alpha'),
        'A)' => array('[A-Z]\)\ ',          1, 'upper-alpha'),
    );
    public function begin()
    {
        $RE = $REul = array();
        foreach ($this->bullets as $desc) {
            $RE[] = $desc[0];
            if (!$desc[1]) $REul[] = $desc[0];
        }
        $this->texy->registerBlockPattern(
            array($this, 'patternList'),
            '#^(?:'.TEXY_MODIFIER_H.'\n)?'          // .{color: red}
          . '('.implode('|', $RE).')\ *\S.*$#mUu',  // item (unmatched)
            'list'
        );
        $this->texy->registerBlockPattern(
            array($this, 'patternDefList'),
            '#^(?:'.TEXY_MODIFIER_H.'\n)?'               // .{color:red}
          . '(\S.*)\:\ *'.TEXY_MODIFIER_H.'?\n'          // Term:
          . '(\ ++)('.implode('|', $REul).')\ *\S.*$#mUu',  // - description
            'list/definition'
        );
    }
    public function patternList($parser, $matches)
    {
        list(, $mMod, $mBullet) = $matches;
        $tx = $this->texy;
        $el = TexyHtml::el();
        $bullet = $min = NULL;
        foreach ($this->bullets as $type => $desc)
            if (preg_match('#'.$desc[0].'#Au', $mBullet)) {
                $bullet = isset($desc[3]) ? $desc[3] : $desc[0];
                $min = isset($desc[3]) ? 2 : 1;
                $el->name = $desc[1] ? 'ol' : 'ul';
                $el->attrs['style']['list-style-type'] = $desc[2];
                if ($desc[1]) { // ol
                    if ($type[0] === '1' && (int) $mBullet > 1)
                        $el->attrs['start'] = (int) $mBullet;
                    elseif ($type[0] === 'a' && $mBullet[0] > 'a')
                        $el->attrs['start'] = ord($mBullet[0]) - 96;
                    elseif ($type[0] === 'A' && $mBullet[0] > 'A')
                        $el->attrs['start'] = ord($mBullet[0]) - 64;
                }
                break;
            }
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $el);
        $parser->moveBackward(1);
        while ($elItem = $this->patternItem($parser, $bullet, FALSE, 'li'))
            $el->addChild($elItem);
        if (count($el->children) < $min) return FALSE;
        if (is_callable(array($tx->handler, 'afterList')))
            $tx->handler->afterList($parser, $el, $mod);
        return $el;
    }
    public function patternDefList($parser, $matches)
    {
        list(, $mMod, , , , $mBullet) = $matches;
        $tx = $this->texy;
        $bullet = NULL;
        foreach ($this->bullets as $type => $desc)
            if (preg_match('#'.$desc[0].'#Au', $mBullet)) {
                $bullet = isset($desc[3]) ? $desc[3] : $desc[0];
                break;
            }
        $el = TexyHtml::el('dl');
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $el);
        $parser->moveBackward(2);
        $patternTerm = '#^\n?(\S.*)\:\ *'.TEXY_MODIFIER_H.'?()$#mUA';
        while (TRUE) {
            if ($elItem = $this->patternItem($parser, $bullet, TRUE, 'dd')) {
                $el->addChild($elItem);
                continue;
            }
            if ($parser->next($patternTerm, $matches)) {
                list(, $mContent, $mMod) = $matches;
                $elItem = TexyHtml::el('dt');
                $mod = new TexyModifier($mMod);
                $mod->decorate($tx, $elItem);
                $elItem->parseLine($tx, $mContent);
                $el->addChild($elItem);
                continue;
            }
            break;
        }
        if (is_callable(array($tx->handler, 'afterDefinitionList')))
            $tx->handler->afterDefinitionList($parser, $el, $mod);
        return $el;
    }
    public function patternItem($parser, $bullet, $indented, $tag)
    {
        $tx =  $this->texy;
        $spacesBase = $indented ? ('\ {1,}') : '';
        $patternItem = "#^\n?($spacesBase)$bullet\\ *(\\S.*)?".TEXY_MODIFIER_H."?()$#mAUu";
        $matches = NULL;
        if (!$parser->next($patternItem, $matches)) return FALSE;
        list(, $mIndent, $mContent, $mMod) = $matches;
        $elItem = TexyHtml::el($tag);
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $elItem);
        $spaces = '';
        $content = ' ' . $mContent; // trick
        while ($parser->next('#^(\n*)'.$mIndent.'(\ {1,'.$spaces.'})(.*)()$#Am', $matches)) {
            list(, $mBlank, $mSpaces, $mContent) = $matches;
            if ($spaces === '') $spaces = strlen($mSpaces);
            $content .= "\n" . $mBlank . $mContent;
        }
        $tmp = $tx->paragraphModule->mode;
        $tx->paragraphModule->mode = FALSE;
        $elItem->parseBlock($tx, $content);
        $tx->paragraphModule->mode = $tmp;
        if ($elItem->getChild(0) instanceof TexyHtml) {
            $elItem->getChild(0)->setName(NULL);
        }
        return $elItem;
    }
} // TexyListModule
