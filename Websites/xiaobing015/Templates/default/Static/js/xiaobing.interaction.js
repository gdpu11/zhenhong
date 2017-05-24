/*==============================================
 *http://www.xiaobing360.com
 * jQuery交互组件 Version 1.0 
 * Copyright 2016 xb, Inc. Author WANGHAO 
 * Licensed under the MIT license
 */

if (typeof jQuery === 'undefined') {
  throw new Error('需要添加jQuery!')
}
/*==============================================
 * jQuery列表交互组件 
 * Copyright 2016 xb, Inc. Author WANGHAO 
 */
+(function($,window, document, undefined) {
  //构造
  var _Interaction = function(element, options){

    var _self = this;
    
    var options =  $.extend({},_Interaction.DEFAULTS, options);
    var trigger = options.trigger;
    var effect;
    var $interactionObj = $('div[role="datalist"]');
    //增加列表布局处理
    var mod;
    $interactionObj.each(function(index){
      _this = $(this)
      effect = _this.attr('effect');//效果类型

      if(effect =='' || effect =='undefined') effect = options.effect;
      var thisObj = $(this);//父对象
      switch(effect)
      {
      case 'fade':
        _self.fade(thisObj,trigger,effect);

        break;
      case 'move':
        _self.move(thisObj,trigger,effect);
       
        break;
      default:
        //n 与 case 1 和 case 2 不同时执行的代码
      }

      //增加列表布局处理 mod =正整数
      mod = _this.attr('mod');//获取配置
      var re = /^[0-9]*[1-9][0-9]*$/;         
      if (re.test(mod))  
      {  
          console.log(mod);
          _this.find('.list-item').each(function(index){
            index = index+1
            if(index > 0 &&index%mod==0){
              $(this).addClass('last');
            }
          });
      }
      //console.log(_this);
      
    })

  }

 //配置 
  _Interaction.DEFAULTS = {
    trigger : 'mouseover',//事件类型
    effect : 'fade'
  };

  //交互扩展
  _Interaction.prototype={
    
    fade:function(iaObj,trigger,effect){
      if(trigger == 'mouseover'){

        iaObj.find('.list-item').hover(function(){

            $(this).find('.hover-box').stop(true,true).fadeIn(300);
            $(this).addClass('active');

          },function(){
            $(this).find('.hover-box').stop(true,true).fadeOut(300);
            $(this).removeClass('active');
        })

      }
    },
    move:function(iaObj,trigger,effect){

      if(trigger == 'mouseover'){
        
      }
      //sliphover 特效
      iaObj.sliphover({
          target:'img'
          //backgroundColorAttr: 'data-background'
      });

    } 
     
  }

  //注册到全局
  $(window).on('load', function () {
     window._Interaction = new _Interaction();
  })
  
})(jQuery,window, document);
/*==============================================
 * jQuery导航交互组件 
 * Copyright 2016 xb, Inc. Author WANGHAO 
 */
+(function($,window, document, undefined) {
  
    var _Nav = function(element,options){
    var _self = this;
    var options =  $.extend({},_Nav.DEFAULTS, options);//获取配置
    //var $_Nav =  $(element);
    var thisObj = $(this);
    var trigger = options.trigger;
    var $_Nav = $('ul[role="navigation"]');
    //遍历导航，添加特效
    $_Nav.each(function(index){
      var effect = $(this).attr('effect'); //获取特效类型
      if(effect == '' || effect == 'undefined' ) effect = options.effect;
      var thisObj = $(this);
      //根据特效类型 调用不同js函数
      switch(effect)
      {
      case 'slideDown':
        _self.slideDown(thisObj,trigger,effect);
        
        break;
      case 'moveDown':

        _self.moveDown(thisObj,trigger,effect);
        break;
      case 'move':

        _self.move(thisObj,trigger,effect);
        
        break;

      default:
      }

    })
    

  }
  //配置
  _Nav.DEFAULTS = {
    trigger:'mouseover',//鼠标事件
    effect:'slideDown'//特效类型
  } 
  //扩展交互效果
  _Nav.prototype = {
    slideDown:function(iaObj,trigger,effect){
      iaObj.find('>li').hover(function(){
        //鼠标来临
        $(this).addClass("on");
        $(this).find("ul").stop(true,true).slideDown();
        },
        function(){
          //鼠标离去
          $(this).find("ul").stop(true,true).slideUp();
          $(this).removeClass("on");       
        }
      )
    },
    moveDown:function(iaObj,trigger,effect){
      //封装移动下拉特效
      var moveDowneffect = function(){
         
        this.$aline = iaObj.find("#aline");//导航下划线
         //if (this.$aline.length<1) iaObj.append('<span id="aline"></span>'); //创建一个下划线
        this.posCurrent = 0;      //初始化焦点li坐标
        if(iaObj.find("li.active").length){
          
           posCurrent = iaObj.find("li.active").position().left;
           
        }else{
          
          this.$aline.hide();
          
        }
        //绑定hover事件
        iaObj.find('>li').hover(function(){
          //鼠标来临
          var pos = $(this).position().left;//当前列坐标
          var $currentW = $(this).width(); //当前列宽
          var $currentMl = $(this).css("margin-left");
              $currentMl =  parseInt($currentMl);
          var currentIndex = $(this).parent().find("li.active").index();  //焦点索引
          //$currentW = $currentW/3; //设置aline width
          $(this).parent().find("#aline").stop().animate({'left':pos,'width':$currentW},300);
          $(this).addClass("on");
          $(this).find("ul").stop(true,true).slideDown(300);
          var thisIndex = $(this).index();
          if(currentIndex !== thisIndex){
            $(this).parent().find("li.active").addClass('move');
          }
          //console.log(currentIndex)
          },
          function(){
            //鼠标离去
            var pos = $(this).position().left;//当前坐标
              if($(this).parent().find("li.active").length){
                posCurrent = $(this).parent().find("li.active").position().left;//焦点的左坐标
                
                var $currentW =  $(this).parent().find("li.active").width(); //焦点的列宽
                var $currentMl = $(this).parent().find("li.active").css("margin-left"); //焦点的margin-left
                  $currentMl =  parseInt($currentMl);
                  //$currentW =  $currentW/3;
                
              }else {
                
                posCurrent =0;
                var $currentW=0;
                var $currentMl=0;
              
              };
              
            //子菜单延时执行 解决bug
            $(this).find("ul").delay(300).queue(function(){
              $(this).hide();
            })
            //
            $(this).parent().find("#aline").stop().animate({'left':posCurrent,'width':$currentW},300);
            $(this).removeClass("on");
            $(this).parent().find("li.active").removeClass('move');
            
          }
        )
        this.init();//初始化
      }
      //初始化
      moveDowneffect.prototype.init = function(){
        //初始化导航
        if(iaObj.find(">li.active").length){
          var $awidth = iaObj.find("li.active").width(); //得到菜单列宽
          var $aheight = iaObj.find(">li").eq(0).height();
          var posCurrent2 =  iaObj.find("li.active").position().left;
          var $currentMl = iaObj.find("li.active").css("margin-left");  //当前marginleft
              $currentMl =  parseInt($currentMl);
              //$awidth = $awidth/3;  
          this.$aline.stop().animate({"width":$awidth,"left":(posCurrent2)},300)//初始化
        }

      }

      return new moveDowneffect();
  
    },
    move:function(iaObj,trigger,effect){
      //封装移动下拉特效
      var moveDowneffect = function(){
         
        this.$aline = iaObj.find("#aline");//导航下划线
         //if (this.$aline.length<1) iaObj.append('<span id="aline"></span>'); //创建一个下划线
        this.posCurrent = 0;      //初始化焦点li坐标
        if(iaObj.find("li.active").length){
          
           posCurrent = iaObj.find("li.active").position().left;
           
        }else{
          
          this.$aline.hide();
          
        }
        //绑定hover事件
        iaObj.find('>li').hover(function(){
          //鼠标来临
          var pos = $(this).position().left;//当前列坐标
          var $currentW = $(this).width(); //当前列宽
          var $currentMl = $(this).css("margin-left");
              $currentMl =  parseInt($currentMl);
          var currentIndex = $(this).parent().find("li.active").index();  //焦点索引

          $(this).parent().find("#aline").stop().animate({'left':pos,'width':$currentW},300);
          $(this).addClass("on");
          // 子菜单
          $(this).find('.en').stop().animate({top:'-27px'},'swing')
          $(this).find('.zh').stop().animate({top:'-27px'},'swing')

          },
          function(){
            //鼠标离去
            var pos = $(this).position().left;//当前坐标
              if($(this).parent().find("li.active").length){
                posCurrent = $(this).parent().find("li.active").position().left;//焦点的左坐标
                
                var $currentW =  $(this).parent().find("li.active").width(); //焦点的列宽
                var $currentMl = $(this).parent().find("li.active").css("margin-left"); //焦点的margin-left
                  $currentMl =  parseInt($currentMl);
                  //$currentW =  $currentW/3;
                
              }else {
                
                posCurrent =0;
                var $currentW=0;
                var $currentMl=0;
              
              };
              
            //子菜单延时执行 解决bug
            $(this).find('.en').stop().animate({top:'0'},'swing')
            $(this).find('.zh').stop().animate({top:'0'},'swing')
            
            //line
            $(this).parent().find("#aline").stop().animate({'left':posCurrent,'width':$currentW},300);
            $(this).removeClass("on");
            $(this).parent().find("li.active").removeClass('move');
            
          }
        )
        this.init();//初始化
      }
      //初始化
      moveDowneffect.prototype.init = function(){
        //初始化导航
        if(iaObj.find(">li.active").length){
          var $awidth = iaObj.find("li.active").width(); //得到菜单列宽
          var $aheight = iaObj.find(">li").eq(0).height();
          var posCurrent2 =  iaObj.find("li.active").position().left;
          var $currentMl = iaObj.find("li.active").css("margin-left");  //当前marginleft
              $currentMl =  parseInt($currentMl);
              //$awidth = $awidth/3;  
          this.$aline.stop().animate({"width":$awidth,"left":(posCurrent2)},300).fadeIn()//初始化
        }

      }

      return new moveDowneffect();
  
    }


  }

  $(window).on('load', function () {
     window._Nav = new _Nav('#nav');
  })
  
})(jQuery,window, document);

/*==============================================
 * 图片滚动 
 * //UI&UE Dept. mengjia 080623
 *  var scrollPic_02 = new ScrollPic(); 
   scrollPic_02.scrollContId   = "ISL_Cont_1"; //内容容器ID 
   scrollPic_02.arrLeftId      = "LeftArr";//左箭头ID 
   scrollPic_02.arrRightId     = "RightArr"; //右箭头ID 
   scrollPic_02.frameWidth     = 908;//显示框宽度 
   scrollPic_02.pageWidth      = 900; //翻页宽度 
   scrollPic_02.speed          = 10; //移动速度(单位毫秒，越小越快) 
   scrollPic_02.space          = 50; //每次移动像素(单位px，越大越快) 
   scrollPic_02.autoPlay       = false; //自动播放 (默认为停止,去掉false则自动播放) 
   scrollPic_02.autoPlayTime   = 1; //自动播放间隔时间(秒) 
   scrollPic_02.initialize(); //初始化 
 */
var sina = {
    $: function(objName) {
        if (document.getElementById) {
            return eval('document.getElementById("' + objName + '")')
        } else {
            return eval('document.all.' + objName)
        }
    },
    isIE: navigator.appVersion.indexOf("MSIE") != -1 ? true : false,
    addEvent: function(l, i, I) {
        if (l.attachEvent) {
            l.attachEvent("on" + i, I)
        } else {
            l.addEventListener(i, I, false)
        }
    },
    delEvent: function(l, i, I) {
        if (l.detachEvent) {
            l.detachEvent("on" + i, I)
        } else {
            l.removeEventListener(i, I, false)
        }
    },
    readCookie: function(O) {
        var o = "",
            l = O + "=";
        if (document.cookie.length > 0) {
            var i = document.cookie.indexOf(l);
            if (i != -1) {
                i += l.length;
                var I = document.cookie.indexOf(";", i);
                if (I == -1) I = document.cookie.length;
                o = unescape(document.cookie.substring(i, I))
            }
        };
        return o
    },
    writeCookie: function(i, l, o, c) {
        var O = "",
            I = "";
        if (o != null) {
            O = new Date((new Date).getTime() + o * 3600000);
            O = "; expires=" + O.toGMTString()
        };
        if (c != null) {
            I = ";domain=" + c
        };
        document.cookie = i + "=" + escape(l) + O + I
    },
    readStyle: function(I, l) {
        if (I.style[l]) {
            return I.style[l]
        } else if (I.currentStyle) {
            return I.currentStyle[l]
        } else if (document.defaultView && document.defaultView.getComputedStyle) {
            var i = document.defaultView.getComputedStyle(I, null);
            return i.getPropertyValue(l)
        } else {
            return null
        }
    }
};
//滚动图片构造函数
function ScrollPic(scrollContId, arrLeftId, arrRightId, dotListId) {
    this.scrollContId = scrollContId;
    this.arrLeftId = arrLeftId;
    this.arrRightId = arrRightId;
    this.dotListId = dotListId;
    this.dotClassName = "dotItem";
    this.dotOnClassName = "dotItemOn";
    this.dotObjArr = [];
    this.pageWidth = 0;
    this.frameWidth = 0;
    this.speed = 10;
    this.space = 10;
    this.pageIndex = 0;
    this.autoPlay = true;
    this.autoPlayTime = 5;
    var _autoTimeObj, _scrollTimeObj, _state = "ready";
    this.stripDiv = document.createElement("DIV");
    this.listDiv01 = document.createElement("DIV");
    this.listDiv02 = document.createElement("DIV");
    if (!ScrollPic.childs) {
        ScrollPic.childs = []
    };
    this.ID = ScrollPic.childs.length;
    ScrollPic.childs.push(this);
    this.initialize = function() {
        if (!this.scrollContId) {
            throw new Error("必须指定scrollContId.");
            return
        };
        this.scrollContDiv = sina.$(this.scrollContId);
        if (!this.scrollContDiv) {
            throw new Error("scrollContId不是正确的对象.(scrollContId = \"" + this.scrollContId + "\")");
            return
        };
        this.scrollContDiv.style.width = this.frameWidth + "px";
        this.scrollContDiv.style.overflow = "hidden";
        this.listDiv01.innerHTML = this.listDiv02.innerHTML = this.scrollContDiv.innerHTML;
        this.scrollContDiv.innerHTML = "";
        this.scrollContDiv.appendChild(this.stripDiv);
        this.stripDiv.appendChild(this.listDiv01);
        this.stripDiv.appendChild(this.listDiv02);
        this.stripDiv.style.overflow = "hidden";
        this.stripDiv.style.zoom = "1";
        this.stripDiv.style.width = "32766px";
        this.listDiv01.style.cssFloat = "left";
        this.listDiv02.style.cssFloat = "left";
        sina.addEvent(this.scrollContDiv, "mouseover", Function("ScrollPic.childs[" + this.ID + "].stop()"));
        sina.addEvent(this.scrollContDiv, "mouseout", Function("ScrollPic.childs[" + this.ID + "].play()"));
        if (this.arrLeftId) {
            this.arrLeftObj = sina.$(this.arrLeftId);
            if (this.arrLeftObj) {
                sina.addEvent(this.arrLeftObj, "mousedown", Function("ScrollPic.childs[" + this.ID + "].rightMouseDown()"));
                sina.addEvent(this.arrLeftObj, "mouseup", Function("ScrollPic.childs[" + this.ID + "].rightEnd()"));
                sina.addEvent(this.arrLeftObj, "mouseout", Function("ScrollPic.childs[" + this.ID + "].rightEnd()"))
            }
        };
        if (this.arrRightId) {
            this.arrRightObj = sina.$(this.arrRightId);
            if (this.arrRightObj) {
                sina.addEvent(this.arrRightObj, "mousedown", Function("ScrollPic.childs[" + this.ID + "].leftMouseDown()"));
                sina.addEvent(this.arrRightObj, "mouseup", Function("ScrollPic.childs[" + this.ID + "].leftEnd()"));
                sina.addEvent(this.arrRightObj, "mouseout", Function("ScrollPic.childs[" + this.ID + "].leftEnd()"))
            }
        };
        if (this.dotListId) {
            this.dotListObj = sina.$(this.dotListId);
            if (this.dotListObj) {
                var pages = Math.round(this.listDiv01.offsetWidth / this.frameWidth + 0.4),
                    i, tempObj;
                for (i = 0; i < pages; i++) {
                    tempObj = document.createElement("span");
                    this.dotListObj.appendChild(tempObj);
                    this.dotObjArr.push(tempObj);
                    if (i == this.pageIndex) {
                        tempObj.className = this.dotClassName
                    } else {
                        tempObj.className = this.dotOnClassName
                    };
                    tempObj.title = "第" + (i + 1) + "页";
                    sina.addEvent(tempObj, "click", Function("ScrollPic.childs[" + this.ID + "].pageTo(" + i + ")"))
                }
            }
        };
        if (this.autoPlay) {
            this.play()
        }
    };
    this.leftMouseDown = function() {
        if (_state != "ready") {
            return
        };
        _state = "floating";
        _scrollTimeObj = setInterval("ScrollPic.childs[" + this.ID + "].moveLeft()", this.speed)
    };
    this.rightMouseDown = function() {
        if (_state != "ready") {
            return
        };
        _state = "floating";
        _scrollTimeObj = setInterval("ScrollPic.childs[" + this.ID + "].moveRight()", this.speed)
    };
    this.moveLeft = function() {
        if (this.scrollContDiv.scrollLeft + this.space >= this.listDiv01.scrollWidth) {
            this.scrollContDiv.scrollLeft = this.scrollContDiv.scrollLeft + this.space - this.listDiv01.scrollWidth
        } else {
            this.scrollContDiv.scrollLeft += this.space
        };
        this.accountPageIndex()
    };
    this.moveRight = function() {
        if (this.scrollContDiv.scrollLeft - this.space <= 0) {
            this.scrollContDiv.scrollLeft = this.listDiv01.scrollWidth + this.scrollContDiv.scrollLeft - this.space
        } else {
            this.scrollContDiv.scrollLeft -= this.space
        };
        this.accountPageIndex()
    };
    this.leftEnd = function() {
        if (_state != "floating") {
            return
        };
        _state = "stoping";
        clearInterval(_scrollTimeObj);
        var fill = this.pageWidth - this.scrollContDiv.scrollLeft % this.pageWidth;
        this.move(fill)
    };
    this.rightEnd = function() {
        if (_state != "floating") {
            return
        };
        _state = "stoping";
        clearInterval(_scrollTimeObj);
        var fill = -this.scrollContDiv.scrollLeft % this.pageWidth;
        this.move(fill)
    };
    this.move = function(num, quick) {
        var thisMove = num / 5;
        if (!quick) {
            if (thisMove > this.space) {
                thisMove = this.space
            };
            if (thisMove < -this.space) {
                thisMove = -this.space
            }
        };
        if (Math.abs(thisMove) < 1 && thisMove != 0) {
            thisMove = thisMove >= 0 ? 1 : -1
        } else {
            thisMove = Math.round(thisMove)
        };
        var temp = this.scrollContDiv.scrollLeft + thisMove;
        if (thisMove > 0) {
            if (this.scrollContDiv.scrollLeft + thisMove >= this.listDiv01.scrollWidth) {
                this.scrollContDiv.scrollLeft = this.scrollContDiv.scrollLeft + thisMove - this.listDiv01.scrollWidth
            } else {
                this.scrollContDiv.scrollLeft += thisMove
            }
        } else {
            if (this.scrollContDiv.scrollLeft - thisMove <= 0) {
                this.scrollContDiv.scrollLeft = this.listDiv01.scrollWidth + this.scrollContDiv.scrollLeft - thisMove
            } else {
                this.scrollContDiv.scrollLeft += thisMove
            }
        };
        num -= thisMove;
        if (Math.abs(num) == 0) {
            _state = "ready";
            if (this.autoPlay) {
                this.play()
            };
            this.accountPageIndex();
            return
        } else {
            this.accountPageIndex();
            setTimeout("ScrollPic.childs[" + this.ID + "].move(" + num + "," + quick + ")", this.speed)
        }
    };
    this.next = function() {
        if (_state != "ready") {
            return
        };
        _state = "stoping";
        this.move(this.pageWidth, true)
    };
    this.play = function() {
        if (!this.autoPlay) {
            return
        };
        clearInterval(_autoTimeObj);
        _autoTimeObj = setInterval("ScrollPic.childs[" + this.ID + "].next()", this.autoPlayTime * 1000)
    };
    this.stop = function() {
        clearInterval(_autoTimeObj)
    };
    this.pageTo = function(num) {
        if (_state != "ready") {
            return
        };
        _state = "stoping";
        var fill = num * this.frameWidth - this.scrollContDiv.scrollLeft;
        this.move(fill, true)
    };
    this.accountPageIndex = function() {
        this.pageIndex = Math.round(this.scrollContDiv.scrollLeft / this.frameWidth);
        if (this.pageIndex > Math.round(this.listDiv01.offsetWidth / this.frameWidth + 0.4) - 1) {
            this.pageIndex = 0
        };
        var i;
        for (i = 0; i < this.dotObjArr.length; i++) {
            if (i == this.pageIndex) {
                this.dotObjArr[i].className = this.dotClassName
            } else {
                this.dotObjArr[i].className = this.dotOnClassName
            }
        }
    }
};















