jQuery(document).ready(function($){$("#envira-albums-settings input:not([type=hidden]), #envira-albums-settings select").conditions([{conditions:{element:'[name="_eg_album_data[config][breadcrumbs_enabled]"]',type:"checked",operator:"is"},actions:{if:[{element:"#envira-breadcrumbs-separator-box",action:"show"}],else:[{element:"#envira-breadcrumbs-separator-box",action:"hide"}]}}])});