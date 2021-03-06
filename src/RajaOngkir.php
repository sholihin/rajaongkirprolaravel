<?php

namespace sholihin\rajaongkirprolaravel;

use sholihin\rajaongkirprolaravel\app\Provinsi;
use sholihin\rajaongkirprolaravel\app\Kota;
use sholihin\rajaongkirprolaravel\app\Kecamatan;
use sholihin\rajaongkirprolaravel\app\Cost;
use sholihin\rajaongkirprolaravel\app\Resi;

class RajaOngkir {
	public function Provinsi(){
		return new Provinsi;
	}

	public function Kota(){
		return new Kota;
	}

	public function Kecamatan(){
		return new Kecamatan;
	}

	public function Cost($attributes){
		return new Cost($attributes);
	}

	public function Resi($attributes){
		return new Resi($attributes);
	}
}