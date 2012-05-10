<?php
if (!class_exists('Texy', FALSE)) die();
class TexyPhraseModule extends TexyModule
{
    protected $default = array(
        'phrase/strong+em' => TRUE,  // ***strong+emphasis***
        'phrase/strong' => TRUE,     // **strong**
        'phrase/em' => TRUE,         // //emphasis//
        'phrase/em-alt' => TRUE,     // *emphasis*
        'phrase/span' => TRUE,       // "span"
        'phrase/span-alt' => TRUE,   // ~span~
        'phrase/acronym' => TRUE,    // "acro nym"((...))
        'phrase/acronym-alt' => TRUE,// acronym((...))
        'phrase/code' => TRUE,       // `code`
        'phrase/notexy' => TRUE,     // ''....''
        'phrase/quote' => TRUE,      // >>quote<<:...
        'phrase/quicklink' => TRUE,  // ....:LINK
        'phrase/sup-alt' => TRUE,    // superscript^2
        'phrase/sub-alt' => TRUE,    // subscript_3
        'phrase/ins' => FALSE,       // ++inserted++
        'phrase/del' => FALSE,       // --deleted--
        'phrase/sup' => FALSE,       // ^^superscript^^
        'phrase/sub' => FALSE,       // __subscript__
        'phrase/cite' => FALSE,      // ~~cite~~
        'deprecated/codeswitch' => FALSE,// `=...
    );
    public $tags = array(
        'phrase/strong' => 'strong', // or 'b'
        'phrase/em' => 'em', // or 'i'
        'phrase/em-alt' => 'em',
        'phrase/ins' => 'ins',
        'phrase/del' => 'del',
        'phrase/sup' => 'sup',
        'phrase/sup-alt' => 'sup',
        'phrase/sub' => 'sub',
        'phrase/sub-alt' => 'sub',
        'phrase/span' => 'a',
        'phrase/span-alt' => 'a',
        'phrase/cite' => 'cite',
        'phrase/acronym' => 'acronym',
        'phrase/acronym-alt' => 'acronym',
        'phrase/code'  => 'code',
        'phrase/quote' => 'q',
        'phrase/quicklink' => 'a',
    );
    public $linksAllowed = TRUE;
    public function begin()
    {
        $tx = $this->texy;
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\*)\*\*\*(?![\s*])(.+)'.TEXY_MODIFIER.'?(?<![\s*])\*\*\*(?!\*)'.TEXY_LINK.'??()#Uus',
            'phrase/strong+em'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\*)\*\*(?![\s*])(.+)'.TEXY_MODIFIER.'?(?<![\s*])\*\*(?!\*)'.TEXY_LINK.'??()#Uus',
            'phrase/strong'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<![/:])\/\/(?![\s/])(.+)'.TEXY_MODIFIER.'?(?<![\s/])\/\/(?!\/)'.TEXY_LINK.'??()#Uus',
            'phrase/em'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\*)\*(?![\s*])(.+)'.TEXY_MODIFIER.'?(?<![\s*])\*(?!\*)'.TEXY_LINK.'??()#Uus',
            'phrase/em-alt'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\+)\+\+(?![\s+])([^\r\n]+)'.TEXY_MODIFIER.'?(?<![\s+])\+\+(?!\+)()#Uu',
            'phrase/ins'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<![<-])\-\-(?![\s>-])([^\r\n]+)'.TEXY_MODIFIER.'?(?<![\s<-])\-\-(?![>-])()#Uu',
            'phrase/del'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\^)\^\^(?![\s^])([^\r\n]+)'.TEXY_MODIFIER.'?(?<![\s^])\^\^(?!\^)()#Uu',
            'phrase/sup'
        );
        $tx->registerLinePattern(
            array($this, 'patternSupSub'),
            '#(?<=[a-z0-9])\^([0-9]{1,4})(?![a-z0-9])#Uui',
            'phrase/sup-alt'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\_)\_\_(?![\s_])([^\r\n]+)'.TEXY_MODIFIER.'?(?<![\s_])\_\_(?!\_)()#Uu',
            'phrase/sub'
        );
        $tx->registerLinePattern(
            array($this, 'patternSupSub'),
            '#(?<=[a-z])\_([0-9]{1,3})(?![a-z0-9])#Uui',
            'phrase/sub-alt'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\")\"(?!\s)([^\"\r]+)'.TEXY_MODIFIER.'?(?<!\s)\"(?!\")'.TEXY_LINK.'??()#Uu',
            'phrase/span'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\~)\~(?!\s)([^\~\r]+)'.TEXY_MODIFIER.'?(?<!\s)\~(?!\~)'.TEXY_LINK.'??()#Uu',
            'phrase/span-alt'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\~)\~\~(?![\s~])([^\r\n]+)'.TEXY_MODIFIER.'?(?<![\s~])\~\~(?!\~)'.TEXY_LINK.'??()#Uu',
            'phrase/cite'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\>)\>\>(?![\s>])([^\r\n]+)'.TEXY_MODIFIER.'?(?<![\s<])\<\<(?!\<)'.TEXY_LINK.'??()#Uu',
            'phrase/quote'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!\")\"(?!\s)([^\"\r\n]+)'.TEXY_MODIFIER.'?(?<!\s)\"(?!\")\(\((.+)\)\)()#Uu',
            'phrase/acronym'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(?<!['.TEXY_CHAR.'])(['.TEXY_CHAR.']{2,})()\(\((.+)\)\)#Uu',
            'phrase/acronym-alt'
        );
        $tx->registerLinePattern(
            array($this, 'patternNoTexy'),
            '#(?<!\')\'\'(?![\s\'])([^'.TEXY_MARK.'\r\n]*)(?<![\s\'])\'\'(?!\')()#Uu',
            'phrase/notexy'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#\`(\S[^'.TEXY_MARK.'\r\n]*)'.TEXY_MODIFIER.'?(?<!\s)\`'.TEXY_LINK.'??()#Uu',
            'phrase/code'
        );
        $tx->registerLinePattern(
            array($this, 'patternPhrase'),
            '#(['.TEXY_CHAR.'0-9@\#$%&.,_-]+)()(?=:\[)'.TEXY_LINK.'()#Uu',
            'phrase/quicklink'
        );
        $tx->registerBlockPattern(
            array($this, 'patternCodeSwitch'),
            '#^`=(none|code|kbd|samp|var|span)$#mUi',
            'deprecated/codeswitch'
        );
    }
    public function patternPhrase($parser, $matches, $phrase)
    {
        list(, $mContent, $mMod, $mLink) = $matches;
        $tx = $this->texy;
        $mod = new TexyModifier($mMod);
        $link = NULL;
        $parser->again = $phrase !== 'phrase/code' && $phrase !== 'phrase/quicklink';
        if ($phrase === 'phrase/span' || $phrase === 'phrase/span-alt') {
            if ($mLink == NULL) {
                if (!$mMod) return FALSE; // means "..."
            } else {
                $link = $tx->linkModule->factoryLink($mLink, $mMod, $mContent);
            }
        } elseif ($phrase === 'phrase/acronym' || $phrase === 'phrase/acronym-alt') {
            $mod->title = trim(Texy::unescapeHtml($mLink));
        } elseif ($phrase === 'phrase/quote') {
            $mod->cite = $tx->quoteModule->citeLink($mLink);
        } elseif ($mLink != NULL) {
            $link = $tx->linkModule->factoryLink($mLink, NULL, $mContent);
        }
        if (is_callable(array($tx->handler, 'phrase'))) {
            $res = $tx->handler->phrase($parser, $phrase, $mContent, $mod, $link);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solve($phrase, $mContent, $mod, $link);
    }
    public function patternSupSub($parser, $matches, $phrase)
    {
        list(, $mContent) = $matches;
        $mod = new TexyModifier();
        $link = NULL;
        if (is_callable(array($this->texy->handler, 'phrase'))) {
            $res = $this->texy->handler->phrase($parser, $phrase, $mContent, $mod, $link);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solve($phrase, $mContent, $mod, $link);
    }
    public function solve($phrase, $content, $mod, $link)
    {
        $tx = $this->texy;
        $tag = isset($this->tags[$phrase]) ? $this->tags[$phrase] : NULL;
        if ($tag === 'a')
            $tag = $link && $this->linksAllowed ? NULL : 'span';
        if ($phrase === 'phrase/code')
            $content = $tx->protect(Texy::escapeHtml($content), Texy::CONTENT_TEXTUAL);
        if ($phrase === 'phrase/strong+em') {
            $el = TexyHtml::el($this->tags['phrase/strong']);
            $el->add($this->tags['phrase/em'], $content);
            $mod->decorate($tx, $el);
        } elseif ($tag) {
            $el = TexyHtml::el($tag)->setText($content);
            $mod->decorate($tx, $el);
            if ($tag === 'q') $el->attrs['cite'] = $mod->cite;
        } else {
            $el = $content; // trick
        }
        if ($link) return $tx->linkModule->solve($link, $el);
        return $el;
    }
    public function patternNoTexy($parser, $matches)
    {
        list(, $mContent) = $matches;
        return $this->texy->protect(Texy::escapeHtml($mContent), Texy::CONTENT_TEXTUAL);
    }
    public function patternCodeSwitch($parser, $matches)
    {
        $this->tags['phrase/code'] = $matches[1];
        return "\n";
    }
} // TexyPhraseModule
