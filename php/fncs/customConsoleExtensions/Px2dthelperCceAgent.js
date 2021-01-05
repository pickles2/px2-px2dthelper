window.Px2dthelperCceAgent = function(options){
	options = options || {};
	options.lang = options.lang || 'ja';
	options.gpiBridge = options.gpiBridge || function(request, callback){};
	this.lang = function(){return options.lang;}
	this.gpi = function(request, callback){
		options.gpiBridge(request, callback);
	}
}