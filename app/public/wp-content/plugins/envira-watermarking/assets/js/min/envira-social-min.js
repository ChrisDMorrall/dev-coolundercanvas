jQuery(document).ready(function($){$(document).on("click",".envira-social-buttons a",function(i){i.preventDefault();var t=$(this).attr("href"),e=$(this).parent().data("width"),a=$(this).parent().data("height"),n=$(this).parent().data("network");if("#"==t){var o=$("img.envirabox-image").attr("src"),r=$("img.envirabox-image").attr("alt"),c=$("img.envirabox-image").data("envira-title"),s=$("img.envirabox-image").data("envira-caption"),d=$("img.envirabox-image").data("envira-index");switch(n){case"facebook":t="https://www.facebook.com/dialog/feed?app_id="+envira_social.facebook_app_id+"&display=popup&link="+window.location.href.split("#")[0]+"&picture="+o+"&name="+c+"&caption="+s+"&description="+r+"&redirect_uri="+window.location.href.split("#")[0];break;case"twitter":t="https://twitter.com/intent/tweet?text="+s+"&url="+window.location.href.split("#")[0]+"?envira_social_index="+d;break;case"google":t="https://plus.google.com/share?url="+window.location.href.split("#")[0]+"?envira_social_index="+d;break;case"pinterest":t="http://pinterest.com/pin/create/button/?url="+window.location.href.split("#")[0]+"&media="+o+"&description="+s}}var l=window.open(t,"Share","width="+e+",height="+a);return!1}),$("div.envira-gallery-item-inner").hover(function(){$("div.envira-social-buttons",$(this)).fadeIn()},function(){$("div.envira-social-buttons",$(this)).fadeOut()})});