//<!--

function checkDependencesBetweenCriterions(checkedNodes, xmlString) {
    var parser = new DOMParser();
    var xml = parser.parseFromString(xmlString, "text/xml");
    var allDependences = xml.evaluate("dependences-between-criterions/dependence", xml.documentElement, null,
        XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);
    if(allDependences !== null) {
        var dependence = allDependences.iterateNext();
        while(dependence) {
            var typeOfDependence = xml.evaluate("@type", dependence, null, XPathResult.STRING_TYPE, null)
                .stringValue;
            if(typeOfDependence == 'at-least-one') {
                var dependentCriterions = xml.evaluate(".//dependent-criterion", dependence, null,
                    XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);
                if(dependentCriterions !== null) {
                    var isOK = false;
                    var criterion = dependentCriterions.iterateNext();
                    while(criterion) {
                        var criterionID = xml.evaluate("@id", criterion, null, XPathResult.STRING_TYPE, null)
                            .stringValue;
                        for(var nodeID in checkedNodes) {
                            var pattern = new RegExp(criterionID);
                            if(checkedNodes[nodeID].search(pattern) != -1) {
                                isOK = true;
                            }
                        }
                        criterion = dependentCriterions.iterateNext();
                    }
                    if(!isOK) {
                        badDependenceBetweenCriterions(dependence, xml, "at-least-one");
                        return false;
                    }
                }
            }
            else if(typeOfDependence == 'each') {
                var dependentCriterions = xml.evaluate(".//dependent-criterion", dependence, null,
                    XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);
                if(dependentCriterions !== null) {
                    var dependenceIsOK = true;
                    var criterion = dependentCriterions.iterateNext();
                    while(criterion) {
                        var criterionIsOK = false;
                        var criterionID = xml.evaluate("@id", criterion, null, XPathResult.STRING_TYPE, null)
                            .stringValue;
                        for(var nodeID in checkedNodes) {
                            var pattern = new RegExp(criterionID);
                            if(checkedNodes[nodeID].search(pattern) != -1) {
                                criterionIsOK = true;
                            }
                        }
                        criterion = dependentCriterions.iterateNext();
                        dependenceIsOK = dependenceIsOK && criterionIsOK;
                    }
                    if(!dependenceIsOK) {
                        badDependenceBetweenCriterions(dependence, xml, "each");
                        return false;
                    }
                }
            }
            dependence = allDependences.iterateNext();
        }
    }
    return true;
}

//todo what is faster: xpath request or array iteration?

function checkDependencesBetweenClasses(checkedNodes, xmlString) {
    var parser = new DOMParser();
    var xml = parser.parseFromString(xmlString, "text/xml");
    var alreadySeen = [];
    for(var node in checkedNodes) {
        var nodeID = checkedNodes[node].split('_')[0];
        if(alreadySeen.indexOf(nodeID) == -1) {
            alreadySeen.push(nodeID);
            var criterion = xml.evaluate("dependences-between-classes/dependence/dependent-criterion[@id='" + nodeID + "']",
                xml.documentElement, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
            if(criterion != null) {
                var criterionSiblings = xml.evaluate('following-sibling::* | preceding-sibling::*', criterion, null,
                    XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);
                if(criterionSiblings != null) {
                    var oneSibling = criterionSiblings.iterateNext();
                    while(oneSibling) {
                        var isOK = false;
                        alreadySeen.push(oneSibling);
                        var siblingID = xml.evaluate('@id', oneSibling, null, XPathResult.STRING_TYPE, null).stringValue;
                        for(var someNodeID in checkedNodes) {
                            var pattern = new RegExp(siblingID);
                            if(checkedNodes[someNodeID].search(pattern) != -1) {
                                isOK = true;
                            }
                        }
                        if(!isOK) {
                            badDependenceBetweenClasses(siblingID, xml);
                            return false;
                        }
                        oneSibling = criterionSiblings.iterateNext();
                    }
                }
            }
        }
    }
    return true;
}

//-->