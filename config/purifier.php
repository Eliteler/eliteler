<?php

/**
 * Ok, glad you are here
 * first we get a config instance, and set the settings
 * $config = HTMLPurifier_Config::createDefault();
 * $config->set('Core.Encoding', $this->config->get('purifier.encoding'));
 * $config->set('Cache.SerializerPath', $this->config->get('purifier.cachePath'));
 * if ( ! $this->config->get('purifier.finalize')) {
 *     $config->autoFinalize = false;
 * }
 * $config->loadArray($this->getConfig());
 *
 * You must NOT delete the default settings
 * anything in settings should be compacted with params that needed to instance HTMLPurifier_Config.
 *
 * @link http://htmlpurifier.org/live/configdoc/plain.html
 */

return [
    'encoding'           => 'UTF-8',
    'finalize'           => true,
    'ignoreNonStrings'   => false,
    'cachePath'          => storage_path('app/purifier'),
    'cacheFileMode'      => 0755,
    'settings'      => [
        'default' => [
            'HTML.Doctype'             => 'XHTML 1.0 Transitional',
            'HTML.Allowed'             => 'iframe[src|width|height|class|frameborder],div[style|class],p[style|class],span[style|class],b,strong,i,em,u,a[href|title|style|class],ul[style|class],ol[style|class],li[style|class],br,img[style|width|height|alt|src|class],h1[style|class],h2[style|class],h3[style|class],h4[style|class],h5[style|class],h6[style|class],blockquote,pre,font[color],table[style|class],td[style|class],tr[style|class],tbody[style|class]',
            "HTML.SafeIframe"          => true,
            "URI.SafeIframeRegexp"     => "%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/|api.soundcloud.com/tracks/)%",
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align,line-height,width,margin-left',
            'CSS.MaxImgLength' => null,
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => true,
        ],
        'test'    => [
            'Attr.EnableID' => 'true',
        ],
        "youtube" => [
            "HTML.SafeIframe"      => 'true',
            "URI.SafeIframeRegexp" => "%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%",
        ],
        'custom_definition' => [
            'id'  => 'html5-definitions',
            'rev' => 1,
            'debug' => false,
            'elements' => [
                ['section', 'Block', 'Flow', 'Common'],
                ['nav',     'Block', 'Flow', 'Common'],
                ['article', 'Block', 'Flow', 'Common'],
                ['aside',   'Block', 'Flow', 'Common'],
                ['header',  'Block', 'Flow', 'Common'],
                ['footer',  'Block', 'Flow', 'Common'],

                // Content model actually excludes several tags, not modelled here
                ['address', 'Block', 'Flow', 'Common'],
                ['hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common'],

                ['figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common'],
                ['figcaption', 'Inline', 'Flow', 'Common'],

                ['video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
                    'src' => 'URI',
                    'type' => 'Text',
                    'width' => 'Length',
                    'height' => 'Length',
                    'poster' => 'URI',
                    'preload' => 'Enum#auto,metadata,none',
                    'controls' => 'Bool',
                ]],
                ['source', 'Block', 'Flow', 'Common', [
                    'src' => 'URI',
                    'type' => 'Text',
                ]],

                ['s',    'Inline', 'Inline', 'Common'],
                ['var',  'Inline', 'Inline', 'Common'],
                ['sub',  'Inline', 'Inline', 'Common'],
                ['sup',  'Inline', 'Inline', 'Common'],
                ['mark', 'Inline', 'Inline', 'Common'],
                ['wbr',  'Inline', 'Empty', 'Core'],

                ['ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
                ['del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
            ],
            'attributes' => [
                ['iframe', 'allowfullscreen', 'Bool'],
                ['table', 'height', 'Text'],
                ['td', 'border', 'Text'],
                ['th', 'border', 'Text'],
                ['tr', 'width', 'Text'],
                ['tr', 'height', 'Text'],
                ['tr', 'border', 'Text'],
            ],
        ],
        'custom_attributes' => [
            ['a', 'target', 'Enum#_blank,_self,_target,_top'],
        ],
        'custom_elements' => [
            ['u', 'Inline', 'Inline', 'Common'],
        ],
        // ✅ Add <script> support with src only
        'HTML.Allowed' => 'iframe[src|width|height|class|frameborder],div[style|class],p[style|class],span[style|class],b,strong,i,em,u,a[href|title|style|class],ul[style|class],ol[style|class],li[style|class],br,img[style|width|height|alt|src|class],h1[style|class],h2[style|class],h3[style|class],h4[style|class],h5[style|class],h6[style|class],blockquote,pre,font[color],table[style|class],td[style|class],tr[style|class],tbody[style|class],script[defer|src|type]',
        'URI.AllowedSchemes' => ['http' => true, 'https' => true, 'data' => true],
        'HTML.SafeIframe' => true,
        'HTML.SafeScript' => false, // ✅ Enable safe <script src>
        'URI.SafeIframeRegexp' => "%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/|api.soundcloud.com/tracks/)%",

        // ✅ Optionally allow style blocks (with safe type)
        'HTML.AllowedElements' => 'style,script', // optional
        'Attr.AllowedFrameTargets' => ['_blank', '_self', '_top', '_parent'],

        // ✅ Your current CSS is mostly okay — allow a bit more if needed
        'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,padding,color,background-color,text-align,line-height,width,margin-left,height,margin-right,margin-top,margin-bottom,display,position,border,border-radius',

        'AutoFormat.AutoParagraph' => false,
        'AutoFormat.RemoveEmpty'   => true,
        'custom_js' => [
            'HTML.SafeScripting' => ['script'],
            'HTML.SafeIframe' => true,
            'URI.SafeIframeRegexp' => '%^(https?:)?//(www\.)?(guestwoo\.com)%', // Allow GuestWoo only
            'HTML.Allowed' => 'script[src|defer],div,span,b,strong,i,br,p', // Add allowed tags/attrs
            'Attr.AllowedFrameTargets' => ['_blank'],
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty' => true,
        ],
    ],
];
