PageBot.prototype.locateElementBySizzle = function(locator, inDocument){
	var results = [];
	window.Sizzle(locator, inDocument, results);
	return results.length > 0 ? results[0] : null;
}
