<?php

namespace Surzhikov\Location;

use \Dadata\DadataClient;

class LocationQueryBuilder
{
	private int $limit;
	private string $address;
	private string $from_bound;
	private string $to_bound;
	private string $fias_id;
	private string $kladr_id;
	private float $latitude;
	private float $longitude;
	private int $radius;
	private array $boost;


	/**
	 * Конструктор класса 
	 */
	public function __construct()
	{
		$this->limit = 20;
		$this->from_bound = 'country';
		$this->to_bound = 'house';
		$this->radius = 100;
		$this->boost = [];
	}


	/**
	 * Добавление фильтра
	 */
	public function where($column, $operator, $value)
	{
		switch ($column) {
			case 'address':
				if (mb_strtolower($operator) === 'like'){
					$this->address = $value;
				} else {
					throw new LocationQueryBuilderException('Unsupported operator ' . $operator . ' for address column. Use "like" operator.');
				}
				break;

			case 'fias':
				if (mb_strtolower($operator) === '='){
					$this->fias = $value;
				} else {
					throw new LocationQueryBuilderException('Unsupported operator ' . $operator . ' for fias column. Use "=" operator.');
				}
				break;

			case 'kladr':
				if (mb_strtolower($operator) === '='){
					$this->kladr = $value;
				} else {
					throw new LocationQueryBuilderException('Unsupported operator ' . $operator . ' for kladr column. Use "=" operator.');
				}
				break;

			case 'latitude':
				if (mb_strtolower($operator) === '='){
					$this->latitude = $value;
				} else {
					throw new LocationQueryBuilderException('Unsupported operator ' . $operator . ' for latitude column. Use "=" operator.');
				}
				break;

			case 'longitude':
				if (mb_strtolower($operator) === '='){
					$this->longitude = $value;
				} else {
					throw new LocationQueryBuilderException('Unsupported operator ' . $operator . ' for longitude column. Use "=" operator.');
				}
				break;

			case 'radius':
				if (mb_strtolower($operator) === '='){
					$this->radius = $value;
				} else {
					throw new LocationQueryBuilderException('Unsupported operator ' . $operator . ' for radius column. Use "=" operator.');
				}
				break;			

			case 'level':
				if (mb_strtolower($operator) === '>='){
					$this->setBound('min', $value);
				} else if (mb_strtolower($operator) === '<='){
					$this->setBound('max', $value);	
				} else {
					throw new LocationQueryBuilderException('Unsupported operator ' . $operator . ' for level column. Use ">=", "<=" operators.');
				}
				break;			

			default:
				throw new LocationQueryBuilderException('Unsupported column ' . $column);
				break;
		}

		return $this;
	}


	/**
	 * Ограничение количества
	 */
	public function limit(int $count)
	{
		$this->limit = $count;
		return $this;
	}


    /**
     * Приоритезировать поиск в локациях по переданным kldar_id
     */
	public function boostByKladr(array $boost)
	{
		foreach ($boost as $b) {
			$this->boost[]= ['kladr_id' => $b];
		}
		return $this;
	}


	/**
	 * Получение коллекции результатов
	 */
	public function get()
	{
		$requestMethod = $this->resolveRequestMethod();

		switch ($requestMethod) {
			case 'geocode':
				return $this->geocodeRequest();
				break;

			case 'findByFias':
				return $this->findByFiasRequest();
				break;

			case 'findByKladr':
				return $this->findByKladrRequest();
				break;

			case 'geolocate':
				return $this->geolocateRequest();
				break;
			
			default:
				throw new LocationQueryBuilderException('Unsupported request method');
				break;
		}

	}

	/**
	 * Получение первого результата
	 */
	public function first()
	{
		$collection = $this->get();
		return $collection->first() ?? null;
	}



	private function resolveRequestMethod()
	{
		if (isset($this->address)) {
			return 'geocode';
		} else if (isset($this->fias_id)) {
			return 'findByFias';
		} else if (isset($this->kladr_id)) {
			return 'findByKladr';
		} else if (isset($this->latitude) && isset($this->longitude) && isset($this->radius)) {
			return 'geolocate';
		}
		throw new LocationQueryBuilderException('Not enough search params');
	}


	/**
	 * Установка значений границ поиска объектов 
	 */
	private function setBound($position, $bound)
	{
		$bounds = ['country', 'region', 'area', 'city', 'settlement', 'street', 'house'];

		if (in_array($bound, $bounds) == false) {
			throw new LocationQueryBuilderException('Unsupported level string. Use one of: ' . implode(', ', $bounds));
		}
		if ($position == 'min') {
			$this->from_bound = $bound;
		} else if ($position == 'max') {
			$this->to_bound = $bound;
		}
	}


	private function geocodeRequest()
	{
		$locationsCollection = collect([]);

		$dadata = new DadataClient(config('dadata.token'), config('dadata.secret'));

		$params = [];
		if ($this->boost != null) {
			$params['locations_boost'] = $this->boost;

		}
		if ($this->from_bound != null) {
			$params['from_bound'] = ['value' => $this->from_bound];
		}
		if ($this->to_bound != null) {
			$params['to_bound'] = ['value' => $this->to_bound];
		}

		try {
			$addresses = $dadata->suggest("address", $this->address, $this->limit, $params);
		} catch (\Throwable $e) {
			if ($e->getMessage() == 'Empty result') {
				return $locationsCollection;
			} else {
				throw new LocationQueryBuilderException('DaData suggest error ' . $e->getMessage());
			}
		}

		foreach ($addresses as $address) {
			$location = new Location;
			$location->fill($address);
			$locationsCollection->push($location);
		}

		return $locationsCollection;
	}




	private function findByFiasRequest()
	{
		throw new LocationQueryBuilderException('Method not supported yet');
	}


	private function findByKladrRequest()
	{
		throw new LocationQueryBuilderException('Method not supported yet');
	}


	private function geolocateRequest()
	{
		throw new LocationQueryBuilderException('Method not supported yet');
	}





}