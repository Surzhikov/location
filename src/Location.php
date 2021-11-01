<?php

namespace Surzhikov\Location;
use \Illuminate\Support\Arr;

class Location
{

	/**
	 * Адрес локации 
	 */
	public string $address;

	/**
	 * Код ФИАС
	 */
	public string $fias;

	/**
	 * Код КЛАДР
	 */
	public string $kladr;

	/**
	 * Уровень фиас
	 */
	public int $fias_level;


	/**
	 * Широта и долгота 
	 */
	public float $latitude;
	public float $longitude;


	/**
	 * Заполнение  
	 */
    public function fill(array $params)
    {
    	$this->address = Arr::get($params, 'value');
    	$this->fias = Arr::get($params, 'data.fias_id');
    	$this->fias_level = Arr::get($params, 'data.fias_level');
    	$this->kladr = Arr::get($params, 'data.kladr_id');
    	$this->latitude = (float) Arr::get($params, 'data.geo_lat');
    	$this->longitude = (float) Arr::get($params, 'data.geo_lon');
    	return $this;
    }



	/**
	 * Добавление фильтра
	 */
    public static function where(string $column, string $operator, $value)
    {
		$builder = new LocationQueryBuilder;
		$builder = $builder->where(...func_get_args());
		return $builder;
    }


	/**
	 * Ограничение количества
	 */
    public static function limit(int $count)
    {
		$builder = new LocationQueryBuilder;
		$builder = $builder->limit(...func_get_args());
		return $builder;
    }

    /**
     * Приоритезировать поиск в локациях по переданным kldar_id
     */
    public static function boostByKladr(array $boost)
    {
		$builder = new LocationQueryBuilder;
		$builder = $builder->limit(...func_get_args());
		return $builder;
    }

}