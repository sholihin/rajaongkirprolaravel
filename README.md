# rajaongkir API For Laravel 5

API ini digunakan ( baru tersedia ) untuk type akun starter

**Instalasi**

Download package dengan composer
```
composer require sholihin/rajaongkirprolaravel
```
atau
```
{
	"require": {
		"sholihin/rajaongkirprolaravel": "^2.0",
	}
}
```

Tambahkan service provider ke config/app.php
```php
'providers' => [
	....
	
	sholihin\rajaongkirprolaravel\RajaOngkirServiceProvider::class,
]
```

Tambahkan juga aliasnya ke config/app.php
```php
'aliases' => [
	....
	
	'RajaOngkir' => sholihin\rajaongkirprolaravel\RajaOngkirFacade::class,
]
```

Buat file rajaongkir.php di folder config secara manual atau jalankan command artisan
```
php artisan vendor:publish
```
jika anda menggunakan command artisan diatas, anda akan dibuatkan file rajaongkir.php di folder config

Tambahkan kode berikut di file .env untuk konfigurasi API rajaongkir
```
RAJAONGKIR_ENDPOINTAPI=isi_base_url_api_akun_anda_disini
RAJAONGKIR_APIKEY=isi_api_key_anda_disini
```
atau anda juga dapat langsung melakukan konfigurasi di file rajaongkir.php di folder config seperti kode berikut.
```php
'end_point_api' => 'isi_base_url_api_akun_anda_disini',
'api_key' => 'isi_api_key_anda_disini',
```

**Contoh Route**
```php
Route::get('/get-provinces', 'ShippingController@getProvinces')->name('get.province');
Route::get('/generate-provinces', 'ShippingController@generateProvinces')->name('generate.province');
Route::get('/get-cities', 'ShippingController@getCities')->name('get.cities');
Route::get('/generate-cities', 'ShippingController@generateCities')->name('generate.cities');
Route::get('/get-city-by-province/{id}', 'ShippingController@getCityByProvince')->name('get.city.by.province');
Route::get('/get-subdistrict-by-city/{id}', 'ShippingController@getSubdistrictByID');
Route::get('/get-subdistrict-by-name/{name}', 'ShippingController@getSubdistrictByName');
Route::get('/generate-subdistrict', 'ShippingController@generateSubdistricts')->name('generate.subdistrict');
Route::get('/get-cost/{origin}/{destination}/{weight}/{courier}', 'ShippingController@getCost')->name('get.cost');
```

**Contoh Controller**
```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use RajaOngkir;
use Illuminate\Support\Facades\DB;
use App\Models\Province;
use App\Models\City;
use App\Models\Subdistrict;

class ShippingController extends Controller
{
    public function getProvinces(){
        $data = RajaOngkir::Provinsi()->all();
        return $data;
    }

    public function getProvinceByName($param){
        $data = RajaOngkir::Provinsi()->search('province', $name = $param)->get();
        return $data;
    }

    public function generateProvinces(){
        $datas = RajaOngkir::Provinsi()->all();
        foreach($datas as $x){
            DB::table('provinces')->insert(
                ['province_id' => $x['province_id'], 'province' => $x['province']]
            );
            echo $x['province'].'<br>';
        }
    }

    public function getCities(){
        $data = RajaOngkir::Kota()->all();
        return $data;
    }

    public function getCityByName($param){
        $data = RajaOngkir::Kota()->search('city_name', $name = $param)->get();
        return $data;
    }

    public function getCityByProvince($id){
        $data = City::where('province_id', $id)->get();
        return $data;
    }
    
    //Jika ingin menyalin ke database lokal (perlu membuat tabel `cities`)
    public function generateCities(){
        $datas = RajaOngkir::Kota()->all();
        foreach($datas as $x){
            DB::table('cities')->insert([
                'city_id' => $x['city_id'],
                'province_id' => $x['province_id'], 
                'type' => $x['type'], 
                'city_name' => $x['city_name'], 
                'postal_code' => $x['postal_code']
            ]);
            echo $x['city_name'].'<br>';
        }
    }

    public function getSubdistrictByName($nama){
        $query = Subdistrict::where('subdistrict_name','like', '%' . $nama . '%')->get();
        $data = array();
        foreach($query as $x){
            $original_data = array('id'=>$x->subdistrict_id, 'value'=>$x->subdistrict_name.', '.$x->type.' '.$x->city.' - '.$x->province);
            array_push($data, $original_data);
        }
        return $data;
    }

    public function getSubdistrictByID($id){
        $data = RajaOngkir::Kecamatan()->byCity($id)->get();
        return $data;
    }

    //Jika ingin menyalin ke database lokal (perlu membuat tabel `subdistricts`)
    public function generateSubdistricts(){
        $datas = City::all();
        foreach($datas as $x){
            $dataDis = $this->getSubdistrictByID($x->city_id);
            foreach($dataDis as $insDis){
                DB::table('subdistricts')->insert([
                    'subdistrict_id' => $insDis['subdistrict_id'],
                    'province_id' => $insDis['province_id'],
                    'province' => $insDis['province'],
                    'city_id' => $insDis['city_id'],
                    'city' => $insDis['city'],
                    'type' => $insDis['type'],
                    'subdistrict_name' => $insDis['subdistrict_name']
                ]);

                // echo $insDis['subdistrict_name'].'<br>';
            }
            echo $x['city_name'].'<br>';
        }
    }
    
    public function getCost($origin, $destination, $weight, $courier){
        $data = RajaOngkir::Cost([
            'origin' 		    => $origin,
            'originType' 	    => 'subdistrict',
            'destination' 	    => $destination,
            'destinationType'   => 'subdistrict',
            'weight' 		    => $weight,
            'courier' 		    => $courier,
        ])->get();

        return $data;
    }
}
```

**Tambahan Database**

***provinces***
```mysql
CREATE TABLE `provinces` (
  `province_id` int(11) NOT NULL,
  `province` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

***cities***
```mysql
CREATE TABLE `cities` (
  `city_id` int(20) NOT NULL,
  `province_id` int(50) NOT NULL,
  `type` varchar(30) DEFAULT NULL,
  `city_name` varchar(255) NOT NULL,
  `postal_code` int(191) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

***subdistricts***
```mysql
CREATE TABLE `subdistricts` (
  `subdistrict_id` int(191) NOT NULL,
  `province_id` int(191) NOT NULL,
  `province` varchar(255) NOT NULL,
  `city_id` int(191) NOT NULL,
  `city` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `subdistrict_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

**Penggunaan**

Ambil data provinsi
```php
$data = RajaOngkir::Provinsi()->all();
```

Ambil jumlah provinsi
```php
$data = RajaOngkir::Provinsi()->count();
```

Ambil data provinsi berdasarkan id provinsi
```php
$data = RajaOngkir::Provinsi()->find($id);
```

Ambil data provinsi berdasarkan nama provinsi
```php
$data = RajaOngkir::Provinsi()->search('province', $name = "ja")->get();
```

Ambil data kota
```php
$data = RajaOngkir::Kota()->all();
```

Ambil jumlah kota
```php
$data = RajaOngkir::Kota()->count();
```

Ambil data kota berdasarkan id kota
```php
$data = RajaOngkir::Kota()->find($id);
```

Ambil data kota berdasarkan nama kota
```php
$data = RajaOngkir::Kota()->search('city_name', $name = "banyu")->get();
```

Ambil data kota berdasarkan provinsi
```php
$data = RajaOngkir::Kota()->byProvinsi($provinsi_id)->get();
```

Ambil jumlah kota berdasarkan provinsi
```php
$data = RajaOngkir::Kota()->byProvinsi($provinsi_id)->count();
```

Ambil data kota berdasarkan nama kota di suatu provinsi
```php
$data = RajaOngkir::Kota()->byProvinsi($provinsi_id)->search('city_name', $name)->get();
```

Ambil data kecamatan berdasarkan kota
```php
$data = RajaOngkir::Kecamatan()->byCity($city_id)->get();
```

Ambil Biaya Pengiriman
```php
$data = RajaOngkir::Cost([
	'origin' 		=> 501,
	'originType' 		=> 'subdistrict',
	'destination' 		=> 574,
	'destinationType'	=> "subdistrict",
	'weight'		=> 1700,
	'courier'		=> 'jne'
])->get();
```


Kunjungi [rajaongkir](http://rajaongkir.com/)

Documentasi akun [pro](https://rajaongkir.com/dokumentasi/pro)
