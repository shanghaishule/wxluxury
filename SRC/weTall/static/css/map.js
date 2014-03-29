/**
 * Created with JetBrains WebStorm.
 * User: 在那花开时节
 * Date: 12-6-15
 * Time: 上午9:23
 * To change this template use File | Settings | File Templates.
 */
var newMap = {a:{},b:{name:"目的地"}};
newMap.directionsDisplay = {};
newMap.directionsService = new google.maps.DirectionsService();

function initMap(mapCenter) {
    newMap.directionsDisplay = new google.maps.DirectionsRenderer();
    var myOptions = {
        zoom:10,
        mapTypeId: google.maps.MapTypeId.ROADMAP, //地图类型
        center: mapCenter   //LatLng 对象
    }
    newMap.map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
    newMap.directionsDisplay.setMap(newMap.map);
}
//导航方法
function calcRoute(start,end) {
    var request = {
        origin:start,  //开始的位置 （A）
        destination:end, //开始的位置 （B）
        travelMode: google.maps.TravelMode.DRIVING   //导航类型 驾驶
    };
    newMap.directionsService.route(request, function(result, status) {
        if (status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(result);
        }
    });
}

function getLatLng(){

    if( navigator.geolocation ) {
        function gpsSuccess(pos){

            if( pos.coords ){
                newMap.a.lat = pos.coords.latitude;
                newMap.a.lng = pos.coords.longitude;
            }
            else{
                newMap.a.lat = pos.latitude;
                newMap.a.lng = pos.longitude;
            }
            var userPositon = new google.maps.LatLng(newMap.a.lat,newMap.a.lng);
            var crsPositon = new google.maps.LatLng(newMap.b.lat,newMap.b.lng);
            initMap(userPositon);
            calcRoute(userPositon,crsPositon);
            addMarker(crsPositon,newMap.map,newMap.b.name);
            addMarker(userPositon,newMap.map, "您当前位置");
        }
        function gpsFail(){

            alert('无法获取您的地理位置信息');
            var obj = new google.maps.LatLng(newMap.b.lat,newMap.b.lng);
            initMap(obj);
            addMarker(obj,newMap.map, newMap.b.name);
        }
        navigator.geolocation.getCurrentPosition(gpsSuccess, gpsFail, {enableHighAccuracy:true, maximumAge: 3000000,timeout:20*1000});
    }
}
//向地图上添加某地标识
function addMarker(location,map,contentString) {
    var marker = new google.maps.Marker({
        position: location,
        'draggable': false,
        'animation': google.maps.Animation.DROP,
        map: map
    });
    var infowindow = new google.maps.InfoWindow({
        content: contentString
    });
    google.maps.event.addListener(marker, 'click', function(){
        infowindow.open(map,marker);
    });
}
//
function getGPS(){
    var requesturl = "";
    var successFunction = function(data) {
        if (data.result) {
            newMap.b.lat = data.data.lat;
            newMap.b.lng = data.data.lng;
            newMap.b.crsname =data.data.name;
            getLatLng();
        } else {
            alert('拉取数据失败，请稍后重试!');
        }
    }
    $.ajax({
            url:requesturl,
            success:successFunction
        }
    );
}

function onload(){
	newMap.a.lat = {$start_point_lat};
	newMap.a.lng = {$start_point_lng};
	newMap.a.name ="我的位置";
	
    newMap.b.lat = 39.9999;
    newMap.b.lng = 116.3964;
    newMap.b.name ="鸟巢体育中心";
    getLatLng();
}

document.addEventListener('onload',onload());