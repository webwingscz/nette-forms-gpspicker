<?php

namespace VojtechDobes\NetteForms;

use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Forms\IControl;


/**
 * Picker of single point
 *
 * @author Vojtěch Dobeš
 */
class GpsPositionPicker extends GpsPicker
{

	const DEFAULT_LAT = 50.083;
	const DEFAULT_LNG = 14.423;

	/** @var float */
	private $lat;

	/** @var float */
	private $lng;


    /**
     * @return array
     */
	protected function getSupportedDrivers(): array
	{
		return array(
			self::DRIVER_GOOGLE,
			self::DRIVER_NOKIA,
			self::DRIVER_OPENSTREETMAP,
			self::DRIVER_SEZNAM,
		);
	}


    /**
     * @return string
     */
	protected function getShape(): string
	{
		return 'point';
	}


    /**
     * @return array
     */
	protected function getParts(): array
	{
		return array(
			'lat' => array(
				'label' => 'Latitude',
				'rules' => array(GpsPicker::MAX_LAT, GpsPicker::MIN_LAT),
				'attrs' => array(
					'step' => 'any',
				),
			),
			'lng' => array(
				'label' => 'Longitude',
				'rules' => array(GpsPicker::MAX_LNG, GpsPicker::MIN_LNG),
				'attrs' => array(
					'step' => 'any',
				),
			),
		);
	}


	public function loadHttpData(): void
	{
		parent::loadHttpData();
		$this->lat = $this->getHttpData(Form::DATA_LINE, '[lat]');
		$this->lng = $this->getHttpData(Form::DATA_LINE, '[lng]');
	}



	/**
	 * Returns coordinates enveloped in Gps instance
	 *
	 * @return GpsPoint
	 */
	public function getValue(): GpsPoint
	{
		return new GpsPoint($this->lat, $this->lng, $this->search);
	}


    /**
     * @param mixed $coordinates
     * @return $this
     */
	public function setValue($coordinates): self
	{
		if ($coordinates === NULL) {
			$this->lat = self::DEFAULT_LAT;
			$this->lng = self::DEFAULT_LNG;
			$this->search = NULL;
		} elseif ($coordinates instanceof GpsPoint || $coordinates instanceof \stdClass) {
			$this->lat = $coordinates->lat;
			$this->lng = $coordinates->lng;
			$this->search = isset($coordinates->address) ? $coordinates->address : NULL;
		} elseif (isset($coordinates['lat'])) {
			$this->lat = (float) $coordinates['lat'];
			$this->lng = (float) $coordinates['lng'];
			$this->search = isset($coordinates['address']) ? $coordinates['address'] : NULL;
		} else {
			list($this->lat, $this->lng) = $coordinates;
			$this->search = isset($coordinates[2]) ? $coordinates[2] : NULL;
		}

		return $this;
	}



/* === Validation =========================================================== */


    /**
     * @param IControl $control
     * @param $maxLat
     * @return bool
     */
	public static function validateMaxLat(IControl $control, $maxLat): bool
	{
		return $control->getValue()->getLat() <= $maxLat;
	}


    /**
     * @param IControl $control
     * @param $maxLng
     * @return bool
     */
	public static function validateMaxLng(IControl $control, $maxLng): bool
	{
		return $control->getValue()->getLng() <= $maxLng;
	}


    /**
     * @param IControl $control
     * @param $minLat
     * @return bool
     */
	public static function validateMinLat(IControl $control, $minLat): bool
	{
		return $control->getValue()->getLat() >= $minLat;
	}


    /**
     * @param IControl $control
     * @param $minLng
     * @return bool
     */
	public static function validateMinLng(IControl $control, $minLng): bool
	{
		return $control->getValue()->getLng() >= $minLng;
	}


    /**
     * @param IControl $control
     * @param array $args
     * @return bool
     */
	public static function validateMaxDistanceFrom(IControl $control, array $args): bool
	{
		list($distance, $point) = $args;
		return $control->getValue()->getDistanceTo(new GpsPoint($point)) <= $distance;
	}


    /**
     * @param IControl $control
     * @param array $args
     * @return bool
     */
	public static function validateMinDistanceFrom(IControl $control, array $args): bool
	{
		list($distance, $point) = $args;
		return $control->getValue()->getDistanceTo(new GpsPoint($point)) >= $distance;
	}



/* === Use helper =========================================================== */


    /**
     * Registers method 'addGpsPicker' adding GpsPositionPicker to form
     *
     * @param string default driver
     * @param string default type
     * @return array
     */
	public static function register(string $driver = GpsPicker::DRIVER_GOOGLE, string $type = GpsPicker::TYPE_ROADMAP): array
	{
		Container::extensionMethod('addGpsPicker', function ($container, $name, $caption = NULL, $options = array()) use ($driver, $type) {
			if (!isset($options['driver'])) {
				$options['driver'] = $driver;
			}
			if (!isset($options['type'])) {
				$options['type'] = $type;
			}
			return $container[$name] = new GpsPositionPicker($caption, $options);
		});
	}

}
