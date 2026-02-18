@extends('front')

@section('content')

<div class="breadcrumb-bar text-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-12">
                <h2 class="breadcrumb-title mb-2">{{__('settings')}}</h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}"><i
                                    class="ti ti-home-2"></i></a></li>
                        <li class="breadcrumb-item">{{__('user')}}</li>
                        <li class="breadcrumb-item active" aria-current="page">{{__('profile_settings')}}</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="breadcrumb-bg">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-01.png') }}" class="breadcrumb-bg-1" alt="Img">
            <img src="{{ asset('front/img/bg/breadcrumb-bg-02.png') }}" class="breadcrumb-bg-2" alt="Img">
        </div>
    </div>
</div>
<?php

            ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
 
 <script>
 var x = document.getElementById("demo");
 
 function getLocation() {
      x.innerHTML = "Please Wait.. We are loading your location";
   if (navigator.geolocation) {
     navigator.geolocation.watchPosition(showPosition);
   } else { 
     x.innerHTML = "Geolocation is not supported by this browser.";
   }
 }
 
 function setv(setvl) {
    const myArray = setvl.split("_");
    document.getElementById("lang").value=myArray[1];
    document.getElementById("lat").value=myArray[0];
 }
 function showPosition(position) {
    document.getElementById("lang").value=position.coords.longitude;
    document.getElementById("lat").value=position.coords.latitude;

    x.innerHTML="Latitude: " + position.coords.latitude + 
    "<br>Longitude: " + position.coords.longitude;
    
 }
 </script>
 
<div class="page-wrapper">
    <div class="content">
        <div class="container">
            <div class="row justify-content-center">

                @include('user.partials.sidebar')

                <div class="col-xl-9 col-lg-8">
                    <h4 class="mb-3">{{__('profile_settings')}}</h4>
                    <h6 class="mb-4">{{__('profile_picture')}}</h6>
                    <form id="userProfileFormokoko" action="{{ route('productlists') }}">
                        
                        <h6>{{__('general_information')}}</h6>
                        <div class="general-info mb-0">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">{{__('first_name')}}<span class="text-danger">
                                                *</span></label>
                                        <select class="form-control" id="loca" name="loca" onchange="setv(this.value)">
                                            <option value="" >Current location</option>
                                            <option value="11.302309809953927_76.93908599701103" >Mettu paplaym</option>
                                            <option value="11.20826101741361_77.361814227366" >Perumanallur</option>
                                            <option value="11.106317169792499_77.36117033732059" >Tiruppur</option>
                                            <option value="10.993719048714459_77.27553296118387" >Palladam</option>

                                            
                                        </select>

                                        <input type="text" id="location">
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCt868kP5jbYdZJEc2veZO-NOOio3ybL38SIVA&libraries=places"></script>
<script>

$(document).ready(function () {
   google.maps.event.addDomListener(window, 'load', initialize);
   getLocation();
});

function initialize() {
    var input = document.getElementById('location');
    var autocomplete = new google.maps.places.Autocomplete(input);
}
</script>

                                        <input type="text" class="form-control" id="lat" name="lat" value="">
                                        <input type="text" class="form-control" id="lang" name="lang" value="">
                                       
                                             <button type="button" onclick="getLocation()">Get My Location</button>
                                             <button >search</button>

                                            <p id="demo"></p>
                                        <span class="text-danger error-text" id="first_name_error"></span>
                                    </div>
                                </div>
                               
                                
                                
                                
                                
                                
                                
                            </div>
                            
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@endsection