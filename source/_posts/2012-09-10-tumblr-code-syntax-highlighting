---
title: Tumblr Code Syntax Highlighting
---
<p>Finally got around to adding code syntax highlighting to my tumblr blog. Thanks to this <a href="http://snippets-of-code.tumblr.com/post/6027484416/adding-syntax-highlighting-into-tumblr" target="_blank">post</a> it was really easy!</p>

<p>In your head tag add the following javascript:</p>

<pre><code>&lt;!-- For Syntax Highlighting --&gt;
&lt;script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js" type="text/javascript"&gt;&lt;/script&gt;
&lt;link rel="stylesheet" type="text/css" href="http://google-code-prettify.googlecode.com/svn/trunk/src/prettify.css"&gt;&lt;/link&gt;  
&lt;script src="http://google-code-prettify.googlecode.com/svn/trunk/src/prettify.js"&gt;&lt;/script&gt;  
&lt;script&gt;
    function styleCode() {
        if (typeof disableStyleCode != 'undefined') { return; }

        var a = false;

        $('pre').each(function() {
            if (!$(this).hasClass('prettyprint')) {
                $(this).addClass('prettyprint');
                a = true;
            }
        });

        if (a) { prettyPrint(); } 
    }

    $(function() {styleCode();});
&lt;/script&gt;
</code></pre>

<p>Then in your add this css:</p>

<pre><code>/* Pretty printing styles. Used with prettify.js. */
/* Vim sunburst theme by David Leibovic */

pre .str, code .str { color: #65B042; } /* string  - green */
pre .kwd, code .kwd { color: #E28964; } /* keyword - dark pink */
pre .com, code .com { color: #AEAEAE; font-style: italic; } /* comment - gray */
pre .typ, code .typ { color: #89bdff; } /* type - light blue */
pre .lit, code .lit { color: #3387CC; } /* literal - blue */
pre .pun, code .pun { color: #fff; } /* punctuation - white */
pre .pln, code .pln { color: #fff; } /* plaintext - white */
pre .tag, code .tag { color: #89bdff; } /* html/xml tag    - light blue */
pre .atn, code .atn { color: #bdb76b; } /* html/xml attribute name  - khaki */
pre .atv, code .atv { color: #65B042; } /* html/xml attribute value - green */
pre .dec, code .dec { color: #3387CC; } /* decimal - blue */

pre.prettyprint, code.prettyprint {
        background-color: #000;
        -moz-border-radius: 8px;
        -webkit-border-radius: 8px;
        -o-border-radius: 8px;
        -ms-border-radius: 8px;
        -khtml-border-radius: 8px;
        border-radius: 8px;
}

pre.prettyprint {
        width: 95%;
        margin: 1em auto;
        padding: 1em !important;
        white-space: pre-wrap;
}

/* Specify class=linenums on a pre to get line numbering */
ol.linenums { margin-top: 0; margin-bottom: 0; color: #AEAEAE; } /* IE indents via margin-left */
li.L0,li.L1,li.L2,li.L3,li.L5,li.L6,li.L7,li.L8 { list-style-type: none }
/* Alternate shading for lines */
li.L1,li.L3,li.L5,li.L7,li.L9 { }

@media print {
  pre .str, code .str { color: #060; }
  pre .kwd, code .kwd { color: #006; font-weight: bold; }
  pre .com, code .com { color: #600; font-style: italic; }
  pre .typ, code .typ { color: #404; font-weight: bold; }
  pre .lit, code .lit { color: #044; }
  pre .pun, code .pun { color: #440; }
  pre .pln, code .pln { color: #000; }
  pre .tag, code .tag { color: #006; font-weight: bold; }
  pre .atn, code .atn { color: #404; }
  pre .atv, code .atv { color: #060; }
}
</code></pre>

<p>That is it. I didn&rsquo;t think it would be that easy!</p>

<p>You can find more themes <a href="http://google-code-prettify.googlecode.com/svn/trunk/styles/index.html" target="_blank">here</a>.</p>