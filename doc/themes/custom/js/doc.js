/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

//pjax
const pjax = new Pjax({
    elements: "a",
    selectors: ["title", ".doc_content", ".Collapsible__content"],
    cacheBust: false,
});

$(function () {
    const config = {
        wheelPropagation: false
    };

    const configureScroll = function () {

        $("pre").each(function () {
            if (!$(this).parents('.graphiql').length) {
                new PerfectScrollbar(this, config);
            }
        });

        if ($('.graphiql .request').length) {
            new PerfectScrollbar('.graphiql .request', config);
        }

        if ($('.graphiql .response').length) {
            new PerfectScrollbar('.graphiql .response', config);
        }
    };

    configureScroll();
    document.addEventListener('pjax:success', configureScroll);

    //Highlighting
    const configureHighlighting = function () {
        $('pre code').each(function (i, block) {
            hljs.highlightBlock(block);
        });
    };
    configureHighlighting();
    document.addEventListener('pjax:success', configureHighlighting);

    const configureTreeNavigation = function () {
        // Tree navigation
        $('.aj-nav').click(function (e) {
            e.preventDefault();
            $(this).parent().siblings().find('ul').slideUp();
            $(this).next().slideToggle();
        });

        // New Tree navigation
        $('ul.Nav > li.has-children > a > .Nav__arrow').click(function () {
            $(this).parent().parent().toggleClass('Nav__item--open');
            return false;
        });

        // Responsive navigation
        $('.Collapsible__trigger').click(function () {
            $('.Collapsible__content').slideToggle();
        });
    }
    configureTreeNavigation();
    document.addEventListener('pjax:success', configureTreeNavigation);
});