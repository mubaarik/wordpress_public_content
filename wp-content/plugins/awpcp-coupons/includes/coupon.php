<?php

define('AWPCP_COUPON_TYPE_AMOUNT', 'amount');
define('AWPCP_COUPON_TYPE_PERCENT', 'percent');

class AWPCP_Coupon {

	private static function create_from_db( $object ) {
		return self::from_object( $object );
	}

	/**
	 * Creates a Subscription Plan from an object, normally returned by
	 * a MySQL function.
	 */
	public static function from_object($object) {
		$coupon = new AWPCP_Coupon();

		$coupon->id = $object->id;
		$coupon->code = $object->code;
		$coupon->discount = $object->discount;
		$coupon->type = $object->type;
		$coupon->redemption_limit = $object->redemption_limit;
		$coupon->redemption_count = $object->redemption_count;
		$coupon->expire_date = $object->expire_date;
		$coupon->enabled = $object->enabled;

		return $coupon;
	}

	public static function from_array($object) {
		$coupon = new AWPCP_Coupon();

		$coupon->id = awpcp_array_data('id', 0, $object);
		$coupon->code = awpcp_array_data('code', '', $object);
		$coupon->discount = awpcp_array_data('discount', 0.0, $object);
		$coupon->type = awpcp_array_data('type', '', $object);
		$coupon->redemption_limit = awpcp_array_data('redemption_limit', 0, $object);
		$coupon->redemption_count = awpcp_array_data('redemption_count', 0, $object);
		$coupon->expire_date = awpcp_array_data('expire_date', null, $object);
		$coupon->enabled = awpcp_array_data('enabled', false, $object);

		return $coupon;
	}
    public static function query($args=array()) {
        global $wpdb;

        extract(wp_parse_args($args, array(
            'fields' => '*',
            'where' => '1 = 1',
            'orderby' => 'id',
            'order' => 'asc',
            'offset' => 0,
            'limit' => 0
        )));

        $query = 'SELECT %s FROM ' . AWPCP_TABLE_COUPONS . ' ';

        if ($fields == 'count') {
            $query = sprintf($query, 'COUNT(id)');
            $limit = 0;
        } else {
            $query = sprintf($query, $fields);
        }

        $query.= sprintf('WHERE %s ', $where);
        $query.= sprintf('ORDER BY %s %s ', $orderby, strtoupper($order));

        if ($limit > 0) {
            $query.= $wpdb->prepare( 'LIMIT %d, %d', $offset, $limit );
        }

        if ($fields == 'count') {
            return $wpdb->get_var($query);
        } else {
            $items = $wpdb->get_results($query);
            $results = array();

            foreach($items as $item) {
                $results[] = self::create_from_db($item);
            }

            return $results;
        }
    }

	public static function find_by_id($id) {
		return AWPCP_Coupon::find_by('id = '. intval($id));
	}

	public static function find_by($where) {
		$results = AWPCP_Coupon::find($where);
		if (!empty($results)) {
			return AWPCP_Coupon::from_object($results[0]);
		}
		return null;
	}

	public static function find($where='1=1', $order='id', $offset=false, $results=false) {
		global $wpdb;

		$query = "SELECT * FROM " . AWPCP_TABLE_COUPONS . " ";
		$query.= "WHERE $where ORDER BY $order ";

		if ( $offset !== false && $results !== false ) {
			$query.= $wpdb->prepare( 'LIMIT %d, %d', $offset, $results );
		} if ( $results !== false ) {
			$query.= $wpdb->prepare( 'LIMIT %d', $results );
		}

		$items = $wpdb->get_results($query);
		$results = array();

		foreach ($items as $item) {
			$results[] = AWPCP_Coupon::from_object($item);
		}

		return $results;
	}

	public static function delete($id) {
		global $wpdb;

		$coupon = AWPCP_Coupon::find_by_id($id);
		if (is_null($coupon)) {
			return __("The Coupon doesn't exist.", 'awpcp-coupons' );
		}

		$query = 'DELETE FROM ' . AWPCP_TABLE_COUPONS . ' WHERE id = %d';
		$result = $wpdb->query($wpdb->prepare($query, $id));

		return $result !== false ? true : $result;
	}

	public function validate(&$errors) {

		if (isset($this->id)) {
			$this->id = intval($this->id);
		}

		if (empty($this->code)) {
			$errors['code'] = __("Coupon Code can't be empty.", 'awpcp-coupons' );
		}

		$types = array(AWPCP_COUPON_TYPE_AMOUNT, AWPCP_COUPON_TYPE_PERCENT);
		if (!in_array(strtolower($this->type), $types)) {
			$errors['type'] = __('Discount type must be Amount or Percent.', 'awpcp-coupons' );
		}
		$this->type = strtolower($this->type);

		if (empty($this->discount)) {
			$errors['discount'] = __("Discount value can't be empty.", 'awpcp-coupons' );
		}

		if ($this->type == AWPCP_COUPON_TYPE_AMOUNT && floatval($this->discount) <= 0.0) {
			$errors['discount'] = __("Discount must be greater than 0.00.", 'awpcp-coupons' );
		}

		if ($this->type == AWPCP_COUPON_TYPE_PERCENT && floatval($this->discount) <= 0.0) {
			$errors['discount'] = __("Discount must be greater than 0%%.", 'awpcp-coupons' );
		}

		if ($this->type == AWPCP_COUPON_TYPE_PERCENT && floatval($this->discount) > 100.0) {
			$errors['discount'] = __("Discount must be greater less or equal than 100%%.", 'awpcp-coupons' );
		}

		if (intval($this->redemption_limit) < 0) {
			$errors['redemption_limit'] = __("Allowed Ad Count must be greater or equal than 0.", 'awpcp-coupons' );
		}

		if (isset($this->redemption_count) && is_int($this->redemption_count)) {
			$this->redemption_count = intval($this->redemption_count);
		}

		if (empty($this->expire_date)) {
			$errors['expire_date'] = __("Expire Date can't be empty.", 'awpcp-coupons' );
		}

		// XXX: this doesn't belong here, its a validation for
		// the Create Coupon form only, not the Coupon model
		$timestamp = strtotime($this->expire_date);
		$today = date('Y-M-d', current_time('timestamp'));
		$today_timestamp = strtotime($today);
		if ($timestamp < $today_timestamp) {
			$errors['expire_date'] = __("Expire Date must be set <b>to</b> or <b>after</b> $today.", 'awpcp-coupons' );
		}
		$this->expire_date = date('Y-m-d', $timestamp);

		$this->enabled = intval($this->enabled);

		if (!empty($errors)) {
			return false;
		}
		return true;
	}

	public function save(&$errors=array()) {
		global $wpdb;

		if (!$this->validate($errors)) {
			return false;
		}

		$data = array('code' => $this->code,
					  'discount' => $this->discount,
					  'type' => $this->type,
					  'redemption_limit' => $this->redemption_limit,
					  'redemption_count' => $this->redemption_count,
					  'expire_date' => $this->expire_date,
					  'enabled' => $this->enabled);

		$format = array('code' => '%s',
						'discount' => '%f',
						'type' => '%s',
						'redemption_limit' => '%d',
						'redemption_count' => '%d',
						'expire_date' => '%s',
						'enabled' => '%d');

		if (empty($this->id)) {
			$result = $wpdb->insert(AWPCP_TABLE_COUPONS, $data, $format);
			$this->id = $wpdb->insert_id;
		} else {
			$result = $wpdb->update(AWPCP_TABLE_COUPONS, $data, array('id' => $this->id), $format);
		}

		return $result;
	}

	public function format_discount() {
		if ($this->type == AWPCP_COUPON_TYPE_AMOUNT) {
			return sprintf("-%0.2f", $this->discount);
		} else if ($this->type == AWPCP_COUPON_TYPE_PERCENT) {
			return sprintf("%0.1f%% OFF", $this->discount);
		}
	}

	public function apply_discount($amount) {
		switch ($this->type) {
			case AWPCP_COUPON_TYPE_AMOUNT:
				$amount = $amount - $this->discount;
				break;
			case AWPCP_COUPON_TYPE_PERCENT:
				$amount = ((100 - $this->discount) * $amount) / 100;
				$amount = number_format($amount, 2);
				break;
		}

		$amount = ($amount < 0) ? 0 : $amount;

		return $amount;
	}

	public function get_discount_amount($amount) {
		switch ($this->type) {
			case AWPCP_COUPON_TYPE_AMOUNT:
				return $this->discount;

			case AWPCP_COUPON_TYPE_PERCENT:
				return number_format($amount * ($this->discount / 100), 2);

			default:
				return 0;
		}
	}

	public function get_expire_date() {
		return awpcp_datetime( 'awpcp-date', strtotime( $this->expire_date ) );
	}

	public function has_expired() {
		$timestamp = strtotime($this->expire_date);
		$today = strtotime(date('Y-M-d', current_time('timestamp')));
		return $timestamp < $today;
	}
}
