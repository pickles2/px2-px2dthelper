window.Px2dthelperCceAgent = function(options){
	var onBroadcast = function(message){};
	options = options || {};
	options.elm = options.elm || false;
	options.lang = options.lang || 'ja';
	options.appearance = options.appearance || 'auto';
	options.gpiBridge = options.gpiBridge || function(request, callback){
		console.error('This app is not support `gpi()`.');
	};
	options.onPxCmd = options.onPxCmd || function(path, params, options, callback){
		console.error('This app is not support `pxCmd()`.');
	};
	options.onEditContent = options.onEditContent || function(target){
		console.error('This app is not support `editContent()`.');
	};
	options.onEditThemeLayout = options.onEditThemeLayout || function(target){
		console.error('This app is not support `editThemeLayout()`.');
	};
	options.onOpenInBrowser = options.onOpenInBrowser || function(path){
		console.error('This app is not support `openInBrowser()`.');
	};

	this.elm = function(){return options.elm;}
	this.lang = function(){return options.lang;}
	this.appearance = function(){return options.appearance;}
	this.gpi = function(request, callback){
		options.gpiBridge(request, function(res){
			var error = null;
			if(!res){
				callback(null, {
					message: 'gpiBridge() returns no value.',
				});
				return;
			}else if(!res.result){
				error = {
					message: `GPI Error: ${res.message}`,
				};
			}
			callback(res.response, error);
		});
	}
	this.pxCmd = function(path, _options, callback){
		options.onPxCmd(path, _options, callback);
	}
	this.putBroadcastMessage = function(message){
		onBroadcast(message);
	};
	this.onBroadcast = function(callback){
		onBroadcast = callback;
	};
	this.editContent = function(targetPath){
		options.onEditContent(targetPath);
	};
	this.editThemeLayout = function(targetPath, themeId, layoutId){
		options.onEditThemeLayout(targetPath, themeId, layoutId);
	};
	this.openInBrowser = function(path){
		options.onOpenInBrowser(path);
	};
}