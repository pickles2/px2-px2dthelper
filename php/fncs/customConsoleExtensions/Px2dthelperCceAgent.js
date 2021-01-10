window.Px2dthelperCceAgent = function(options){
	options = options || {};
	options.elm = options.elm || false;
	options.lang = options.lang || 'ja';
	options.gpiBridge = options.gpiBridge || function(request, callback){};
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
}