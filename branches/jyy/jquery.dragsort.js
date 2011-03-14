(function(a){a.fn.dragsort=function(c){var d=a.extend({},a.fn.dragsort.defaults,c);var b=[];var f=null,e=null;if(this.selector){a("head").append("<style type='text/css'>"+(this.selector.split(",").join(" "+d.dragSelector+",")+" "+d.dragSelector)+" { cursor: pointer; }</style>")}this.each(function(h,g){if(a(g).is("table")&&a(g).children().size()==1&&a(g).children().is("tbody")){g=a(g).children().get(0)}var j={draggedItem:null,placeHolderItem:null,pos:null,offset:null,offsetLimit:null,scroll:null,container:g,init:function(){a(this.container).attr("data-listIdx",h).mousedown(this.grabItem).find(d.dragSelector).css("cursor","pointer");a(this.container).children(d.itemSelector).each(function(i){a(this).attr("data-itemIdx",i)})},grabItem:function(n){if(n.which!=1||a(n.target).is(d.dragSelectorExclude)){return}var q=n.target;while(!a(q).is("[data-listIdx='"+a(this).attr("data-listIdx")+"'] "+d.dragSelector)){if(q==this){return}q=q.parentNode}if(f!=null&&f.draggedItem!=null){f.dropItem()}f=b[a(this).attr("data-listIdx")];f.draggedItem=a(q).closest(d.itemSelector);f.draggedItem.addClass("dragged");var k=parseInt(f.draggedItem.css("marginTop"));var p=parseInt(f.draggedItem.css("marginLeft"));f.offset=f.draggedItem.offset();f.offset.top=n.pageY-f.offset.top+(isNaN(k)?0:k)-1;f.offset.left=n.pageX-f.offset.left+(isNaN(p)?0:p)-1;if(!d.dragBetween){var m=a(f.container).outerHeight()==0?Math.max(1,Math.round(0.5+a(f.container).children(d.itemSelector).size()*f.draggedItem.outerWidth()/a(f.container).outerWidth()))*f.draggedItem.outerHeight():a(f.container).outerHeight();f.offsetLimit=a(f.container).offset();f.offsetLimit.right=f.offsetLimit.left+a(f.container).outerWidth()-f.draggedItem.outerWidth();f.offsetLimit.bottom=f.offsetLimit.top+m-f.draggedItem.outerHeight()}var l=f.draggedItem.height();var i=f.draggedItem.width();var o=f.draggedItem.attr("style");f.draggedItem.attr("data-origStyle",o?o:"");if(d.itemSelector=="tr"){f.draggedItem.children().each(function(){a(this).width(a(this).width())});f.placeHolderItem=f.draggedItem.clone().attr("data-placeHolder",true);f.draggedItem.after(f.placeHolderItem);f.placeHolderItem.children().each(function(){a(this).css({borderWidth:0,width:a(this).width()+1,height:a(this).height()+1}).html("&nbsp;")})}else{f.draggedItem.after(d.placeHolderTemplate);f.placeHolderItem=f.draggedItem.next().css({height:l,width:i}).attr("data-placeHolder",true)}f.draggedItem.css({position:"absolute",opacity:0.8,"z-index":999,height:l,width:i,cursor:"move"});a(b).each(function(s,r){r.createDropTargets();r.buildPositionTable()});f.scroll={moveX:0,moveY:0,maxX:a(document).width()-a(window).width(),maxY:a(document).height()-a(window).height()};f.scroll.scrollY=window.setInterval(function(){if(d.scrollContainer!=window){a(d.scrollContainer).scrollTop(a(d.scrollContainer).scrollTop()+f.scroll.moveY);return}var r=a(d.scrollContainer).scrollTop();if(f.scroll.moveY>0&&r<f.scroll.maxY||f.scroll.moveY<0&&r>0){a(d.scrollContainer).scrollTop(r+f.scroll.moveY);f.draggedItem.css("top",f.draggedItem.offset().top+f.scroll.moveY+1)}},10);f.scroll.scrollX=window.setInterval(function(){if(d.scrollContainer!=window){a(d.scrollContainer).scrollLeft(a(d.scrollContainer).scrollLeft()+f.scroll.moveX);return}var r=a(d.scrollContainer).scrollLeft();if(f.scroll.moveX>0&&r<f.scroll.maxX||f.scroll.moveX<0&&r>0){a(d.scrollContainer).scrollLeft(r+f.scroll.moveX);f.draggedItem.css("left",f.draggedItem.offset().left+f.scroll.moveX+1)}},10);f.setPos(n.pageX,n.pageY);a(document).bind("selectstart",f.stopBubble);a(document).bind("mousemove",f.swapItems);a(document).bind("mouseup",f.dropItem);if(d.scrollContainer!=window){a(window).bind("DOMMouseScroll mousewheel",f.wheel)}return false},setPos:function(k,o){var m=o-this.offset.top;var l=k-this.offset.left;if(!d.dragBetween){m=Math.min(this.offsetLimit.bottom,Math.max(m,this.offsetLimit.top));l=Math.min(this.offsetLimit.right,Math.max(l,this.offsetLimit.left))}this.draggedItem.parents().each(function(){if(a(this).css("position")!="static"&&(!a.browser.mozilla||a(this).css("display")!="table")){var p=a(this).offset();m-=p.top;l-=p.left;return false}});if(d.scrollContainer==window){o-=a(window).scrollTop();k-=a(window).scrollLeft();o=Math.max(0,o-a(window).height()+5)+Math.min(0,o-5);k=Math.max(0,k-a(window).width()+5)+Math.min(0,k-5)}else{var i=a(d.scrollContainer);var n=i.offset();o=Math.max(0,o-i.height()-n.top)+Math.min(0,o-n.top);k=Math.max(0,k-i.width()-n.left)+Math.min(0,k-n.left)}f.scroll.moveX=k==0?0:k*d.scrollSpeed/Math.abs(k);f.scroll.moveY=o==0?0:o*d.scrollSpeed/Math.abs(o);this.draggedItem.css({top:m,left:l})},wheel:function(k){if((a.browser.safari||a.browser.mozilla)&&f&&d.scrollContainer!=window){var i=a(d.scrollContainer);var l=i.offset();if(k.pageX>l.left&&k.pageX<l.left+i.width()&&k.pageY>l.top&&k.pageY<l.top+i.height()){var m=k.detail?k.detail*5:k.wheelDelta/-2;i.scrollTop(i.scrollTop()+m);k.preventDefault()}}},buildPositionTable:function(){var i=this.draggedItem==null?null:this.draggedItem.get(0);var k=[];a(this.container).children(d.itemSelector).each(function(l,n){if(n!=i){var m=a(n).offset();m.right=m.left+a(n).width();m.bottom=m.top+a(n).height();m.elm=n;k.push(m)}});this.pos=k},dropItem:function(){if(f.draggedItem==null){return}a(f.container).find(d.dragSelector).css("cursor","pointer");f.draggedItem.removeClass("dragged");f.placeHolderItem.before(f.draggedItem);var k=f.draggedItem.attr("data-origStyle");if(k==""){f.draggedItem.removeAttr("style")}else{f.draggedItem.attr("style",k);f.draggedItem.removeAttr("data-origStyle")}f.placeHolderItem.remove();a("[data-dropTarget]").remove();window.clearInterval(f.scroll.scrollY);window.clearInterval(f.scroll.scrollX);var i=false;a(b).each(function(){a(this.container).children(d.itemSelector).each(function(l){if(parseInt(a(this).attr("data-itemIdx"))!=l){i=true;a(this).attr("data-itemIdx",l)}})});if(i||f.draggedItem.parent()[0]!==f.container){d.dragEnd.apply(f.draggedItem)}f.draggedItem=null;a(document).unbind("selectstart",f.stopBubble);a(document).unbind("mousemove",f.swapItems);a(document).unbind("mouseup",f.dropItem);if(d.scrollContainer!=window){a(window).unbind("DOMMouseScroll mousewheel",f.wheel)}return false},stopBubble:function(){return false},swapItems:function(n){if(f.draggedItem==null){return false}f.setPos(n.pageX,n.pageY);var m=f.findPos(n.pageX,n.pageY);var l=f;for(var k=0;m==-1&&d.dragBetween&&k<b.length;k++){m=b[k].findPos(n.pageX,n.pageY);l=b[k]}if(m==-1||a(l.pos[m].elm).attr("data-placeHolder")){return false}if(e==null||e.top>f.draggedItem.offset().top||e.left>f.draggedItem.offset().left){a(l.pos[m].elm).before(f.placeHolderItem)}else{a(l.pos[m].elm).after(f.placeHolderItem)}a(b).each(function(p,o){o.createDropTargets();o.buildPositionTable()});e=f.draggedItem.offset();return false},findPos:function(k,m){for(var l=0;l<this.pos.length;l++){if(this.pos[l].left<k&&this.pos[l].right>k&&this.pos[l].top<m&&this.pos[l].bottom>m){return l}}return -1},createDropTargets:function(){if(!d.dragBetween){return}a(b).each(function(){var k=a(this.container).find("[data-placeHolder]");var i=a(this.container).find("[data-dropTarget]");if(k.size()>0&&i.size()>0){i.remove()}else{if(k.size()==0&&i.size()==0){a(this.container).append(f.placeHolderItem.clone().removeAttr("data-placeHolder").attr("data-dropTarget",true))}}})}};j.init();b.push(j)});return this};a.fn.dragsort.defaults={itemSelector:"li",dragSelector:"li",dragSelectorExclude:"input, textarea, a[href]",dragEnd:function(){},dragBetween:false,placeHolderTemplate:"<li>&nbsp;</li>",scrollContainer:window,scrollSpeed:5}})(jQuery);