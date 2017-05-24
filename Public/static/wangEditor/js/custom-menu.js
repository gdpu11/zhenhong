(function () {

    // 获取 wangEditor 构造函数和 jquery
    var E = window.wangEditor;
    var $ = window.jQuery;

    // 用 createMenu 方法创建菜单
    E.createMenu(function (check) {

        // 定义菜单id，不要和其他菜单id重复。编辑器自带的所有菜单id，可通过『参数配置-自定义菜单』一节查看
        var menuId = 'Qiniu';

        // check将检查菜单配置（『参数配置-自定义菜单』一节描述）中是否该菜单id，如果没有，则忽略下面的代码。
        if (!check(menuId)) {
            return;
        }
		
        // this 指向 editor 对象自身
        var editor = this;

		var lang = editor.config.lang;	
		
        // 创建 menu 对象
        var menu = new E.Menu({
            editor: editor,  // 编辑器对象
            id: menuId,  // 菜单id
            title: '七牛', // 菜单标题

            // 正常状态和选中装下的dom对象，样式需要自定义
            $domNormal: $('<a href="#" tabindex="-1"><i class="wangeditor-menu-img-desktop"></i></a>'),
            $domSelected: $('<a href="#" tabindex="-1" class="selected"><i class="wangeditor-menu-img-omega"></i></a>')
        });

       
		$uploadContent =  $('<div></div>');
		
		
        // UI
        var $uploadIcon = $('<div class="upload-icon-container"><i class="wangeditor-menu-img-upload"></i></div>');
        $uploadContent.append($uploadIcon);
		
		//视频大小
		var $sizeContainer = $('<div style="margin:20px 10px;"></div>');
        var $widthInput = $('<input type="text" value="640" style="width:50px;text-align:center;"/>');
        var $heightInput = $('<input type="text" value="498" style="width:50px;text-align:center;"/>');
        $sizeContainer.append('<span> ' + lang.width + ' </span>')
                      .append($widthInput)
                      .append('<span> px &nbsp;&nbsp;&nbsp;</span>')
                      .append('<span> ' + lang.height + ' </span>')
                      .append($heightInput)
                      .append('<span> px </span>');
		
		var $btnContainer = $('<div></div>');
        var $howToCopy = $('<a href="http://www.kancloud.cn/wangfupeng/wangeditor2/134973" target="_blank" style="display:inline-block;margin-top:10px;margin-left:10px;color:#999;">如何复制视频链接？</a>');
        var $btnSubmit = $('<button class="right">' + lang.submit + '</button>');
        var $btnCancel = $('<button class="right gray">' + lang.cancel + '</button>');
        //$btnContainer.append($howToCopy).append($btnSubmit).append($btnCancel);
		//$uploadContent.append($sizeContainer).append($btnContainer);
		
		
        // 设置id，并暴露
        var btnId = 'upload' + E.random();
        var containerId = 'upload' + E.random();
        $uploadIcon.attr('id', btnId);
        $uploadContent.attr('id', containerId);

        editor.customUploadBtnId = btnId;
        editor.customUploadContainerId = containerId;
		
		
		// 取消按钮
        $btnCancel.click(function (e) {
            e.preventDefault();
            $linkInput.val('');
            menu.dropPanel.hide();
        });

        // 确定按钮
        $btnSubmit.click(function (e) {
            e.preventDefault();
            
            var width = parseInt($widthInput.val());
            var height = parseInt($heightInput.val());
            var $div = $('<div>');
            var html = '<p>{content}</p>';
			alert(width);
            // 执行命令
            editor.command(e, 'insertHtml', html);

        });
		
		
        // 添加panel
        menu.dropPanel = new E.DropPanel(editor, menu, {
            $content: $uploadContent,
            width: 350,
			onRender: function () {
                // 渲染后的回调事件，用于执行自定义上传的init
                // 因为渲染之后，上传面板的dom才会被渲染到页面，才能让第三方空间获取到
				var init = editor.config.customUploadInit;
                init && init.call(editor);
				 
            }
        });
		


        // 增加到editor对象中
        editor.menus[menuId] = menu;
		
		
    });

})();