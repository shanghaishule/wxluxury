/**
 * 系统工具类
 * @author yulipu
 */
window.SysUtil = window.SysUtil || {};
SysUtil.ROOT = '/weTall/static';  // 站点根路径


/**
 * 遮罩层 无依赖任何东西
 */
SysUtil.Lock = function() {
	function YLock() {
		this.inited = false;
		this.mask = null;
		this.maskId = 'y-mask';
		this.version = 0;  // 针对ie6处理
	}
	YLock.prototype = {
		constructor : YLock
		,E : {
			addHandler : function(ele, type, func) {
				if(ele.addEventListener) {
					ele.addEventListener(type, func, false);
				} else if(ele.attachEvent) {
					ele.attachEvent("on" + type, func);	
				} else {
					ele["on" + type] = func;	
				}
			},	
			removeHandler : function(ele, type, func) {
				if(ele.removeEventListener) {
					ele.removeEventListener(type, func, false);
				}	else if(ele.detachEvent) {
					ele.detachEvent("on" + type, func);	
				} else {
					ele["on" + type] = null;	
				}
			}
		}
		,init : function() {
			if(this.inited) return;
			this.mask = document.createElement('div');
			this.mask.id = this.maskId;
			this.mask.setAttribute('id', this.maskId);
			this.mask.style.position = 'fixed';
			this.mask.style.zIndex = '1120';
			this.mask.style.width = '100%';
			this.mask.style.height = '100%';
			this.mask.style.top = 0;
			this.mask.style.left = 0;
			this.mask.style.backgroundColor = '#CCC';
			this.mask.style.filter = 'alpha(opacity=80)';
			this.mask.style.opacity = '0.8';
			// 处理ie
			if(!!window.ActiveXObject) {
				this.version = parseInt(navigator.userAgent.toLowerCase().match(/msie (\d+)/)[1]);
			}
			this.inited = true;
		}
		,processIE6 : function() {
			if(this.version != 6) return;
			var _this = this, scrTop = 0;
			document.body.style.height = '100%';
			document.body.style.width = '100%';
			this.E.addHandler(window, "scroll", function() {
				scrTop = document.documentElement.scrollTop || document.body.scrollTop;
				//ie6遮罩位置
				_this.mask.style.position = 'absolute';
				_this.mask.style.top = scrTop + 'px';
			});
		}
		/**
		 * 对外调用方法
		 */
		,unlock : function() {
			if(document.getElementById(this.maskId)) {
				document.body.removeChild(this.mask);
			}
		}
		,lock : function() {
			this.init();
			document.body.insertBefore(this.mask, document.body.firstChild);
			this.processIE6();
		}
	};
	return new YLock();
}();

/**
 * 消息提示框 需要css文件
 */
SysUtil.Tip = function() {
	function YTip() {
		this.loaded = false;
		this.tmpTipContainer = null;  // 用于存放模板
		this.tmpTipContainerId = 'y-tmpTip';
		this.domSize = null;  // 可见区大小
		this.conf = {
			"cssFile" : SysUtil.ROOT + '/css/ytip.css?1127'
		};
		this.html = 
'<div class="y-tip-container {$bg}">'+
	'<div class="y-unicodeTip y-tip-pullLeft {$icoStyle}">{$ico}</div>'+
	'<div class="y-tip-text" id="y-tip-text">{$msg}</div>'+
	'<div class="y-tip-clearL"></div>'+
'</div>';
	}
	YTip.prototype = {
		constructor : YTip
		,initPostion : function() {
			this.domSize = {
				"width" : Math.max(document.documentElement.clientWidth, document.body.clientWidth),
				"height" : Math.max(document.documentElement.clientHeight, 0/*document.body.clientHeight*/)
			};
			
			this.tmpTipContainer.style.left = parseInt((this.domSize.width - 150) / 2) + 'px';
			this.tmpTipContainer.style.top = parseInt(this.domSize.height / 4) + 'px';
		}
		,init : function() {
			if(this.loaded) return;
			// 创建临时容器
			this.tmpTipContainer = document.createElement('span');
			this.tmpTipContainer.id = this.tmpTipContainerId;
			this.tmpTipContainer.setAttribute('id', this.tmpTipContainerId);
			this.tmpTipContainer.style.position = 'fixed';
			this.tmpTipContainer.style.zIndex = '1127';
			// 加载css
			var linkObj = document.createElement('link');  
			linkObj.setAttribute("rel", "stylesheet");
			linkObj.setAttribute("type", "text/css");
			linkObj.setAttribute("href", this.conf.cssFile);
			document.getElementsByTagName("head")[0].appendChild(linkObj);
			this.loaded = true;
		}
		,replaceVars : function(bg, icoStyle, ico, msg) {
			var s = this.html;
			s = s.replace('{$bg}', bg);  // 背景色
			s = s.replace('{$icoStyle}', icoStyle);  //  图标样式
			s = s.replace('{$ico}', ico);  // 图标
			s = s.replace('{$msg}', msg);  // 消息
			this.tmpTipContainer.innerHTML = s;
		}
		,render : function() {
			var body = document.body;
			body.insertBefore(this.tmpTipContainer, body.firstChild);
			this.initPostion();
		}
		,close : function(t) {
			t = t || 2000;
			var _this = this;
			setTimeout(function(){
				if(document.getElementById(_this.tmpTipContainerId)) {
					document.body.removeChild(_this.tmpTipContainer);
				}
			}, t);
		}
		/**
		 * 对外调用方法
		 */
		,success : function(msg) {
			msg = msg || "";
			this.init();  // 创建临时容器 加载css
			this.replaceVars('y-tip-bgDefault', 'y-ico-success', '&radic;', msg);  // 临时容器赋值innerHTML
			this.render();  // 加入dom
			this.close();
		}
		,error : function(msg) {
			msg = msg || "";
			this.init();  // 创建临时容器 加载css
			this.replaceVars('y-tip-bgDanger', 'y-ico-error', '&times;', msg);  // 临时容器赋值innerHTML
			this.render();  // 加入dom
			this.close();
		}
		,warning : function(msg) {
			msg = msg || "";
			this.init();  // 创建临时容器 加载css
			this.replaceVars('y-tip-bgWarning', 'y-ico-warning', '!', msg);  // 临时容器赋值innerHTML
			this.render();  // 加入dom
			this.close();
		}
	};
	return new YTip();
}();


/**
 * 对话框 需要css文件
 */
SysUtil.Dialog = function() {
	function YDialog() {
		this.loaded = false;
		this.callback = null;  // 回调函数
		this.tmpDialogContainer = null;  // 用于存放模板
		this.tmpDialogContainerId = 'y-tmpDialog';
		this.domSize = null;  // 可见区大小
		this.conf = {
			"cssFile" : SysUtil.ROOT + '/css/ydialog.css?1'
		};
		this.dragConf = {
			"dragable" : true
			,"srcObj" : null
			,"targetObj" : null
			,"diffX" : 0
			,"diffY" : 0
		};
		this.html = 
'<div class="y-modalDialog" id="y-modalDialog">'+
	'<div class="y-modalDialog-title" id="y-modalDialog-title">'+
		'<span class="y-modalDialog-title-text" id="y-modalDialog-title-text">{$title}</span>'+
		'<span class="y-modalDialog-title-close" id="y-modalDialog-title-close" onclick="SysUtil.Dialog.close(-1)">&times;</span>'+
	'</div>'+
	'<div class="y-modalDialog-content" id="y-modalDialog-content">{$content}</div>'+
	'<div class="y-modalDialog-footer" id="y-modalDialog-footer">{$footer}</div>'+
'</div>';
		this.defaultBtnHtml = '<input type="button" value="取消" class="y-modalDialog-btn" onclick="SysUtil.Dialog.close(0)"/>&nbsp;<input type="button" value="确定" class="y-modalDialog-btn" onclick="SysUtil.Dialog.close(1)"/>';
		this.alertBtnHtml = '<input type="button" value="确定" class="y-modalDialog-btn" onclick="SysUtil.Dialog.close(1)"/>';
		
	}
	YDialog.prototype = {
		constructor : YDialog
		/* ------ drag ------ */
		,_mouseup : function(e) {
			var _self = this;
			document.onmousemove = null;
			document.onmouseup = null;
		}
		,_mousemove : function(e) {
			e = e || window.event;
			this.dragConf.targetObj.style.left = e.clientX - this.dragConf.diffX + 'px';
			this.dragConf.targetObj.style.top =  e.clientY - this.dragConf.diffY + 'px';
		}
		,_mousedown : function(e) {
			e = e || window.event;
			var _self = this;
			this.dragConf.diffX = e.clientX - this.dragConf.targetObj.offsetLeft;  //初始化差值
			this.dragConf.diffY = e.clientY - this.dragConf.targetObj.offsetTop;
			document.onmousemove = function(){
				_self._mousemove();
			};
			document.onmouseup = function(){
				_self._mouseup();
			};
		}
		,initDrag : function() {
			if(!this.dragConf.dragable) return;
			var _self = this;
			this.dragConf.srcObj = document.getElementById('y-modalDialog-title');  // title
			this.dragConf.targetObj = this.tmpDialogContainer;  // 目标移动对象
			this.dragConf.srcObj.onmousedown = function(){
				_self._mousedown();
			};
		}
		/* ------ end drag ------ */
		,initPostion : function() {
			this.domSize = {
				"width" : Math.max(document.documentElement.clientWidth, document.body.clientWidth),
				"height" : Math.max(document.documentElement.clientHeight, 0/*document.body.clientHeight*/)
			};
			var scrTop = document.documentElement.scrollTop || document.body.scrollTop;
			var contentWidth = Math.max(this.tmpDialogContainer.scrollWidth, this.tmpDialogContainer.offsetWidth, 220);
			this.tmpDialogContainer.style.left = parseInt((this.domSize.width - contentWidth) / 2) + 'px';
			this.tmpDialogContainer.style.top = parseInt(this.domSize.height / 4) + scrTop + 'px';
		}
		,init : function() {
			if(this.loaded) return;
			// 临时容器
			this.tmpDialogContainer = document.createElement('span');
			this.tmpDialogContainer.id = this.tmpDialogContainerId; 
			this.tmpDialogContainer.setAttribute('id', this.tmpDialogContainerId);
			this.tmpDialogContainer.style.position = 'absolute';
			this.tmpDialogContainer.style.zIndex = '1127';
			// 加载css
			var linkObj = document.createElement('link');  
			linkObj.setAttribute("rel", "stylesheet");
			linkObj.setAttribute("type", "text/css");
			linkObj.setAttribute("href", this.conf.cssFile);
			document.getElementsByTagName("head")[0].appendChild(linkObj);
			this.loaded = true;
		}
		,replaceVars : function(msg, title, btnHtml) {
			var s = this.html;
			s = s.replace('{$title}', title);
			s = s.replace('{$content}', msg);
			s = s.replace('{$footer}', btnHtml);
			this.tmpDialogContainer.innerHTML = s;
		}
		,render : function() {
			var body = document.body;
			body.insertBefore(this.tmpDialogContainer, body.firstChild);
			this.initPostion();  // 计算位置
			
			this.initDrag();  // 拖动处理
		}
		,clearCallback : function() {
			this.callback = null;
		}
		/**
		 * 对外调用方法
		 */
		,show : function(content, title, btnHtml) {
			content = content || "";
			title = title || "";
			btnHtml = btnHtml || "";
			this.clearCallback();  // 清空回调函数
			
			this.init();  // 初始化临时容器 加载css
			this.replaceVars(content, title, btnHtml);  // 临时容器赋值innerHTML
			this.render();  // 临时容器加入dom
			// 遮罩层
			SysUtil.Lock.lock();
		}
		,confirm : function(msg, title, callback) {
			msg = msg || "";
			title = title || "消息";
			this.clearCallback();  // 清空回调函数
			if("function" == typeof callback) {this.callback = callback;}
			var btnHtml = this.defaultBtnHtml;
			
			this.init();  // 初始化临时容器 加载css
			this.replaceVars(msg, title, btnHtml);  // 临时容器赋值innerHTML
			this.render();  // 临时容器加入dom
			// 遮罩层
			SysUtil.Lock.lock();
		}
		,alert : function(msg, title, callback) {
			msg = msg || "";
			title = title || "警告";
			this.clearCallback();  // 清空回调函数
			if("function" == typeof callback) {this.callback = callback;}
			var btnHtml = this.alertBtnHtml;
			
			this.init();  // 初始化临时容器 加载css
			this.replaceVars(msg, title, btnHtml);  // 临时容器赋值innerHTML
			this.render();  // 临时容器加入dom
			// 遮罩层
			SysUtil.Lock.lock();
		}
		,close : function(flag) {
			if(document.getElementById(this.tmpDialogContainerId)) {
				document.body.removeChild(this.tmpDialogContainer);
			}
			// 关闭遮罩
			SysUtil.Lock.unlock();
			if(null != this.callback) {this.callback(flag);}
		}
		,setContent : function(str) {
			// 加入dom后才能调用该方法
			document.getElementById('y-modalDialog-content').innerHTML = str;
			this.initPostion();
		}
	};
	return new YDialog();
}();

/**
 * 画笔 
 * 主要依赖 ypen.css
 */
SysUtil.Pen = function() {
	function YPen() {
		this.isIe = false;
		this.ieVersion = -1;
		this.currentCanvas = null;
		this.currentCanvasId = 'yPenCanvas';
		
		this.loaded = false;
		this.tmpPenContainer = null;  // 临时容器
		this.tmpPenContainerId = 'ytmpPen';
		this.domSize = null;
		this._body = document.body;
		this.conf = {
			"cssFile" : SysUtil.ROOT + "/css/ypen.css?12",  /* 主要 */
			"excanvasFile" : SysUtil.ROOT + "/js/excanvas_r3/excanvas.compiled.js",  /* 动态加载excanvas.js有问题 改为手动在页面添加 */
			"html5File" : SysUtil.ROOT + "/js/html5.js"
		};
		this.colorList = [
			'#000000',
			'#FF0000',
			'#BD5151',
			'#499495',
			'#91ABE1',
			'#1C74AA',
			'#FF00FF',
			'#666666'
		];
		this.penConf = {
			"penColor" : this.colorList[0],
			"penWidth" : 2
		};
		this.fromX = 0;
		this.fromY = 0;
		this.toX = 0;
		this.toY = 0;
		
		this.html = 
			'<div class="y-pen">'+
				'<div class="y-pen-drag"></div>'+
				'<div class="y-pen-help">'+
					'<span class="y-pen-help-info">'+
						'1.点击工具条最右侧可关闭。<br />'+
						'2.点击工具条最左侧并按住可移动。<br />'+
						'3.点击工具条不同色块，切换线条颜色。<br />'+
					'</span>'+
				'</div>'+
				'<div class="y-pen-colors">'+
					'<ul >'+
						'{$lis}'+
					'</ul>'+
				'</div>'+
				'<div class="y-pen-del" onclick="SysUtil.Pen.clear()"></div>'+
				'<div class="y-pen-close" onclick="SysUtil.Pen.close()"></div>'+
			'</div>';
	}
	YPen.prototype = {
		constructor : YPen
		,E : {
			addHandler : function(ele, type, func) {
				if(ele.addEventListener) {
					ele.addEventListener(type, func, false);
				} else if(ele.attachEvent) {
					ele.attachEvent("on" + type, func);	
				} else {
					ele["on" + type] = func;	
				}
			},	
			removeHandler : function(ele, type, func) {
				if(ele.removeEventListener) {
					ele.removeEventListener(type, func, false);
				}	else if(ele.detachEvent) {
					ele.detachEvent("on" + type, func);	
				} else {
					ele["on" + type] = null;	
				}
			}
		}
		,isIe6 : function() {
			if(!!window.ActiveXObject) {
				this.isIe = true;
				this.ieVersion = parseInt(navigator.userAgent.toLowerCase().match(/msie (\d+)/)[1]);
			}
			return 6 == this.ieVersion;
		}
		,drawLine : function() {
			if (this.fromX == 0 || this.fromY == 0 || this.toX == 0 || this.toY == 0) {
				return;
			}
			//alert(this.fromX + '-' + this.fromY + '|' + this.toX + '-' + this.toY);
			if(!this.currentCanvas.getContext) {return;}
			
			var context = this.currentCanvas.getContext("2d");
			var color = this.penConf.penColor;
			
			context.save();
			context.strokeStyle = color;
			context.lineWidth = this.penConf.penWidth;
			context.beginPath();
			context.moveTo(this.fromX ,this.fromY);
			context.lineTo(this.toX, this.toY);
			context.stroke();
			context.closePath();
			context.restore();
		}
		,calculatePos : function() {
			var _this = this;
			
			_this.E.addHandler(_this.currentCanvas, 'mousedown', function(e){
				_this.fromX = e.clientX;
				_this.fromY = e.clientY;
			});
			_this.E.addHandler(_this.currentCanvas, 'mouseup', function(e){
				_this.toX = e.clientX;
				_this.toY = e.clientY;
				//alert(_this.fromX + '-' + _this.fromY + '|' + _this.toX + '-' + _this.toY);
				_this.drawLine();
			});
			//_this.E.addHandler(_this.currentCanvas, 'mousemove', function(){
			//});
			
		}
		,initDomSize : function() {
			this.domSize = {
				"width" : Math.max(document.documentElement.clientWidth, document.body.clientWidth),
				"height" : Math.max(document.documentElement.clientHeight, 0/*document.body.clientHeight*/)
			};
		}
		,initTmpPenContainer : function() {
			this.tmpPenContainer = document.createElement('span');
			this.tmpPenContainer.id = this.tmpPenContainerId;
			this.tmpPenContainer.setAttribute('id', this.tmpPenContainerId);
			this.tmpPenContainer.style.position = 'absolute';
			this.tmpPenContainer.style.zIndex = '1127';
			this.tmpPenContainer.style.left = '20px';
			this.tmpPenContainer.style.bottom = '200px';
		}
		,loadCssFile : function(file) {
			var linkObj = document.createElement('link');  
			linkObj.setAttribute("rel", "stylesheet");
			linkObj.setAttribute("type", "text/css");
			linkObj.setAttribute("href", file);
			document.getElementsByTagName("head")[0].appendChild(linkObj);
		}
		,loadJsFile : function(file) {
			var scriptObj = document.createElement('script'); 
			scriptObj.setAttribute('type', 'text/javascript');
			scriptObj.setAttribute('src', file);
			document.getElementsByTagName('head')[0].appendChild(scriptObj);
		}
		,processIe : function() {
			if(-1 != this.ieVersion && this.ieVersion < 10) {
				// html5
				//this.loadJsFile(this.conf.html5File);
				// js
				//this.loadJsFile(this.conf.excanvasFile);  // 由于动态加载有问题 所以需手动添加excanvas.js文件
				if(window.G_vmlCanvasManager) {
					window.G_vmlCanvasManager.initElement(this.currentCanvas);  // 重新注册动态 canvas, 该方法来自excanvas.js
				}
			}
		}
		,initCanvas : function(append) {
			append = append || false;
			var canvas = document.createElement('canvas');
			canvas.setAttribute('id', this.currentCanvasId);
			canvas.style.position = 'fixed';
			canvas.style.zIndex = '1120';
			canvas.style.top = 0;
			canvas.style.left = 0;
			canvas.style.cursor = 'crosshair';
			canvas.style.width = this.domSize.width + 'px';
			canvas.style.height = this.domSize.height + 'px';
			canvas.setAttribute('width', this.domSize.width);
			canvas.setAttribute('height', this.domSize.height);
			this.currentCanvas = canvas;
			
			if(append) {
				this.append2Dom(this.currentCanvas);
			}
		}
		,append2Dom : function(obj) {
			var body = document.body;
			body.insertBefore(obj, body.firstChild);
		}
		,replaceVars : function() {
			var s = this.html;
			var lis = '', len = this.colorList.length;
			for(var i=0; i<len; i++) {
				lis += '<li style="background-color:'+this.colorList[i]+'" onclick="SysUtil.Pen.setPenColor('+i+')"></li>';
			}
			s = s.replace('{$lis}', lis);
			this.tmpPenContainer.innerHTML = s;
		}
		,render : function() {
			this.append2Dom(this.tmpPenContainer);  // 临时容器加入dom
			
			// 事件绑定
			this.calculatePos();  // canvas加入dom
		}
		,init : function() {
			if(this.loaded) return;
			this.initDomSize();
			// 创建临时容器
			this.initTmpPenContainer();
			// 加载css
			this.loadCssFile(this.conf.cssFile);
			
			//canvas 加入dom
			this.initCanvas(true);
			// 处理ie 追加html5 和 excanvas js 此js会遍历页面中的canvas
			if(this.isIe) {
				this.processIe();
			}
			
			this.loaded = true;
		}
		
		/**
		 * 对外方法
		 */
		,setPenColor : function(i) {
			this.penConf.penColor = this.colorList[i];
		}
		,clear : function() {
			if(!this.currentCanvas.getContext) {return;}
			this.currentCanvas.getContext("2d").clearRect(0, 0, this.domSize.width, this.domSize.height);
		}
		,close : function() {
			if(document.getElementById(this.currentCanvasId)) {
				document.body.removeChild(this.currentCanvas);
			}
			if(document.getElementById(this.tmpPenContainerId)) {
				document.body.removeChild(this.tmpPenContainer);
			}
		}
		,show : function() {
			if(document.getElementById(this.tmpPenContainerId)) {return;}
			if(!this.isIe6()) {
				this.init();  // 创建临时容器 加载css canvas加入dom
				this.replaceVars();  // 临时容器赋值
				this.render();  // 临时容器加入dom 
			}
		}
	};
	
	return new YPen();
}();




