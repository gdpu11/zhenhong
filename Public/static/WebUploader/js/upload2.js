(function ($) {
	  var $ = jQuery,
		$list = $('#thelist'),
        $btn = $('#ctlBtn'),
        state = 'pending',
        uploader;
    // 当domReady的时候开始初始化
        uploader = WebUploader.create({

        // 不压缩image
        resize: false,

        // swf文件路径
        swf: 'js/Uploader.swf',

        // 文件接收服务端。
        server: '/admin.php?s=/File/webuploader',

        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '#picker',
		chunked: true,  //是否分片
		chunkSize: 512 * 1024, //每片大小
		disableGlobalDnd: true,
		/*fileNumLimit: 300,
		fileSizeLimit: 200 * 1024 * 1024, // 200 M
		fileSingleSizeLimit: 50 * 1024 * 1024,    // 50 M*/
		accept: {
			title: '选择文件',
			extensions: __ext
		}
		});

	
		// 当有文件添加进来的时候
		uploader.on( 'fileQueued', function( file ) {
			$list.html('<div id="' + file.id + '" class="item">' +
				'<h4 class="info">' + file.name + '</h4>' +
				'<p class="state">等待上传...</p>' +
			'</div>' );

		});

		// 文件上传过程中创建进度条实时显示。
		uploader.on( 'uploadProgress', function( file, percentage ) {
			var $li = $( '#'+file.id ),
				$percent = $li.find('.progress .bar');

			// 避免重复创建
			if ( !$percent.length ) {
				$percent = $('<div class="progress">' +
				  '<div class="bar" role="progressbar"  style="width: 0%">' +
				  '</div>' +
				'</div>').appendTo( $li ).find('.progress-bar');
			}

			$li.find('p.state').text('上传中');

			$percent.css( 'width', percentage * 100 + '%' );
		});

		uploader.on( 'uploadSuccess', function( file ,data) {
			
			$( '#'+file.id ).find('p.state').text('文件合并中');
			
			$("input[name="+__fileid+"]").val(data.id);
			$("input[name=size]").val(data.size);
			
			
		});

		uploader.on( 'uploadError', function( file ) {
			$( '#'+file.id ).find('p.state').text('上传出错');
		});

		uploader.on( 'uploadComplete', function( file ) {
			$( '#'+file.id ).find('.progress').fadeOut();
            $( '#'+file.id ).find('p.state').text('已上传');
		});

		uploader.on( 'all', function( type ) {
			
			if ( type === 'startUpload' ) {
				state = 'uploading';
			} else if ( type === 'stopUpload' ) {
				state = 'paused';
			} else if ( type === 'uploadFinished' ) {
				state = 'done';
			}

			if ( state === 'uploading' ) {
				$btn.text('暂停上传');
			} else {
				$btn.text('开始上传');
			}
		});
		

		$btn.on( 'click', function() {
			
			if ( state === 'uploading' ) {
				uploader.stop();
			} else {
				
				uploader.upload();
				
			}
		});
		
		 uploader.onError = function (code) {
			 if(code ==='Q_TYPE_DENIED'){
				alert("文件格式错误");
			 }else{
                 alert("未知错误:"+code);
             }
           
        };
	
		
})(jQuery);