window.Px2dthelperCceAgent = function(options){
	var onBroadcast = function(message){};
	options = options || {};
	options.elm = options.elm || false;
	options.lang = options.lang || 'ja';
	options.gpiBridge = options.gpiBridge || function(){};
	options.onEditContents = options.onEditContents || function(){};
	options.onEditThemeLayout = options.onEditThemeLayout || function(){};
	this.elm = function(){return options.elm;}
	this.lang = function(){return options.lang;}
	this.gpi = function(request, callback){
		options.gpiBridge(request, function(res){
			if(!res){
				console.error('GPI returns:', res);
				return;
			}
			if(!res.result){
				console.error('GPI Error:', res.message);
			}
			callback(res.response);
		});
	}
	this.putBroadcastMessage = function(message){
		onBroadcast(message);
	};
	this.onBroadcast = function(callback){
		onBroadcast = callback;
	};
	this.editContents = function(target){
		options.onEditContents(target);
	};
	this.editThemeLayout = function(target){
		options.onEditThemeLayout(target);
	};
}