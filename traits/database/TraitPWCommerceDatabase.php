<?php

namespace ProcessWire;

/**
 * Trait PWCommerce Database: Trait class for PWCommerce Database.
 *
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerce Class for PWCommerce
 * Copyright (C) 2024 by Francis Otieno
 * MIT License
 *
 */


trait TraitPWCommerceDatabase
{





	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~ DATABASE  ~~~~~~~~~~~~~~~~~~

	/**
	 * Prepare and execute a PDO GroupBy query.
	 *
	 * @param array $options
	 * @return mixed
	 */
	protected function processQueryGroupBy(array $options) {

		// e.g.
		// SELECT data as product_id, quantity
		// FROM field_pwcommerce_order_line_item
		// WHERE data > 0
		// AND quantity > 20 -- the high quantity threshold
		// GROUP BY product_id, quantity
		// ORDER BY quantity DESC;

		$database = $this->wire('database');

		// >>>>>>>>>>>>>>>>
		// TABLE
		$tablePrefix = "field_";
		$table = $tablePrefix . $options['table'];
		$table = $database->escapeTable($table);

		// >>>>>>>>>>>>>>>>
		// SELECT

		// select column(s)
		/** @var array $selectColumnsArray */
		$selectColumnsArray = $options['select_columns'];
		$selectColumns = implode(", ", $selectColumnsArray);

		$sql = "SELECT $selectColumns FROM `$table`";

		// >>>>>>>>>>>>>>>>
		// WHERE & AND condition(s)
		if (!empty($options['conditions'])) {
			/** @var array $conditionsArray */
			$conditionsArray = $options['conditions'];
			$conditionString = "";
			foreach ($conditionsArray as $indexCount => $condition) {
				// $andConditionsString = "AND "
				$columnName = $condition['column_name'];
				$paramName = !empty($condition['param_identifier']) ? $condition['param_identifier'] : $condition['column_name'];
				$operator = $condition['operator'];
				$columnType = $condition['column_type'];
				if ($indexCount === 0) {
					// $conditionString .= "WHERE pages_id=:page_id "
					$conditionString .= "WHERE {$columnName}{$operator}:{$paramName}";
				} else {
					$conditionString .= " AND {$columnName}{$operator}:{$paramName}";
				}
			}

			// append CONDITION(s)
			$sql .= " {$conditionString}";

		}

		// >>>>>>>>>>>>>>>>
		// GROUP BY
		/** @var array $groupBycolumnsArray */
		$groupBycolumnsArray = $options['group_by_columns'];
		$groupBycolumns = implode(", ", $groupBycolumnsArray);
		// append GROUPBY(s)
		$sql .= " GROUP BY {$groupBycolumns}";

		// >>>>>>>>>>>>>>>>
		// ORDER BY
		if (!empty($options['order_by_columns'])) {
			/** @var array $orderBycolumnsArray */
			$orderBycolumnsArray = $options['order_by_columns'];
			$orderBycolumns = implode(", ", $orderBycolumnsArray);
			if (!empty($options['order_by_descending'])) {
				$orderBycolumns .= " DESC";
			}
			// append ORDERBY(s)
			$sql .= " ORDER BY {$orderBycolumns}";

		}

		// >>>>>>>>>>>>>>>>
		// PREPARE QUERY
		$query = $database->prepare($sql);

		// >>>>>>>>>>>>>>>>
		// BIND QUERY PARAMS
		if (!empty($options['conditions'])) {
			foreach ($conditionsArray as $condition) {
				$paramName = !empty($condition['param_identifier']) ? $condition['param_identifier'] : $condition['column_name'];
				$operator = $condition['operator'];
				$columnValue = $condition['column_value'];
				$columnType = $condition['column_type'];
				// ---------
				// bind our named parameter
				if ($columnType === 'int') {
					$query->bindValue(":{$paramName}", $columnValue, \PDO::PARAM_INT);
				} else {
					$query->bindValue(":{$paramName}", $columnValue, \PDO::PARAM_STR);
				}
			}
		}

		// >>>>>>>>>>>>>>>>
		// EXECUTE
		$query->execute();

		// >>>>>>>>>>>>>>>>
		// FETCH
		// fetch results
		$result = $query->fetchAll(\PDO::FETCH_ASSOC);

		// -----
		return $result;

	}

	/**
	 * Prepare and execute a PDO GroupBy and Sum query.
	 *
	 * @param array $options
	 * @return mixed
	 */
	protected function processQueryGroupBySum(array $options) {

		$database = $this->wire('database');

		// >>>>>>>>>>>>>>>>
		// TABLE
		$tablePrefix = "field_";
		$table = $tablePrefix . $options['table'];
		$table = $database->escapeTable($table);

		// >>>>>>>>>>>>>>>>
		// SELECT
		// select column(s)
		/** @var array $selectColumnsArray */
		$selectColumnsArray = $options['select_columns'];
		$selectColumns = implode(", ", $selectColumnsArray);

		$sql = "SELECT $selectColumns";

		// >>>>>>>>>>>>>>>>
		// SUM
		$sumArray = $options['sum'];
		$sumExpression = $sumArray['expression'];
		$sumColumnAs = $sumArray['summed_column_name'];
		$sql .= ", SUM($sumExpression) $sumColumnAs";

		// >>>>>>>>>>>>>>>>
		// FROM
		$sql .= " FROM `$table`";

		// >>>>>>>>>>>>>>>>
		// JOIN
		if (!empty($options['join'])) {
			/** @var array $joinArray */
			$joinArray = $options['join'];
			$joinTable = $joinArray['table'];
			$joinType = $joinArray['type'];
			$joinCondition = $joinArray['condition'];
			// -----
			// e.g.
			$joinString = "{$joinType} JOIN {$joinTable} ON {$joinCondition}";

			// append JOIN
			$sql .= " {$joinString}";

		}

		// >>>>>>>>>>>>>>>>
		// WHERE & AND condition(s)
		if (!empty($options['conditions'])) {
			/** @var array $conditionsArray */
			$conditionsArray = $options['conditions'];
			$conditionString = "";
			foreach ($conditionsArray as $indexCount => $condition) {
				// $andConditionsString = "AND "
				$columnName = $condition['column_name'];
				$paramName = !empty($condition['param_identifier']) ? $condition['param_identifier'] : $condition['column_name'];
				$operator = $condition['operator'];
				$columnType = $condition['column_type'];
				if ($indexCount === 0) {
					// $conditionString .= "WHERE pages_id=:page_id "
					$conditionString .= "WHERE {$columnName}{$operator}:{$paramName}";
				} else {
					$conditionString .= " AND {$columnName}{$operator}:{$paramName}";
				}
			}

			// append CONDITION(s)
			$sql .= " {$conditionString}";

		}

		// >>>>>>>>>>>>>>>>
		// GROUP BY
		/** @var array $groupBycolumnsArray */
		$groupBycolumnsArray = $options['group_by_columns'];
		$groupBycolumns = implode(", ", $groupBycolumnsArray);
		// append GROUPBY(s)
		$sql .= " GROUP BY {$groupBycolumns}";

		// >>>>>>>>>>>>>>>>
		// ORDER BY
		if (!empty($options['order_by_columns'])) {
			/** @var array $orderBycolumnsArray */
			$orderBycolumnsArray = $options['order_by_columns'];
			$orderBycolumns = implode(", ", $orderBycolumnsArray);
			if (!empty($options['order_by_descending'])) {
				$orderBycolumns .= " DESC";
			}
			// append ORDERBY(s)
			$sql .= " ORDER BY {$orderBycolumns}";
		}

		// >>>>>>>>>>>>>>>>
		// LIMIT
		if (!empty($options['limit'])) {
			$limit = $options['limit'];
			// append LIMIT
			$sql .= " LIMIT {$limit}";
		}

		// >>>>>>>>>>>>>>>>
		// PREPARE QUERY
		$query = $database->prepare($sql);

		// >>>>>>>>>>>>>>>>
		// BIND QUERY PARAMS
		if (!empty($options['conditions'])) {
			foreach ($conditionsArray as $condition) {
				$paramName = !empty($condition['param_identifier']) ? $condition['param_identifier'] : $condition['column_name'];
				$operator = $condition['operator'];
				$columnValue = $condition['column_value'];
				$columnType = $condition['column_type'];
				// ---------
				// bind our named parameter
				if ($columnType === 'int') {
					$query->bindValue(":{$paramName}", $columnValue, \PDO::PARAM_INT);
				} else {
					$query->bindValue(":{$paramName}", $columnValue, \PDO::PARAM_STR);
				}
			}
		}

		// >>>>>>>>>>>>>>>>
		// EXECUTE
		$query->execute();

		// >>>>>>>>>>>>>>>>
		// FETCH
		// fetch results
		$result = $query->fetchAll(\PDO::FETCH_ASSOC);

		// -----
		return $result;

	}

	/**
	 * Prepare and execute a PDO GroupBy and Count query.
	 *
	 * @param array $options
	 * @return mixed
	 */
	protected function processQueryGroupByCount(array $options) {

		$database = $this->wire('database');

		// >>>>>>>>>>>>>>>>
		// TABLE
		$tablePrefix = "field_";
		$table = $tablePrefix . $options['table'];
		$table = $database->escapeTable($table);

		// >>>>>>>>>>>>>>>>
		// SELECT
		// select column(s)
		/** @var array $selectColumnsArray */
		$selectColumnsArray = $options['select_columns'];
		$selectColumns = implode(", ", $selectColumnsArray);

		$sql = "SELECT";

		// >>>>>>>>>>>>>>>>
		// COUNT
		$countArray = $options['count'];
		$countColumn = $countArray['count_column'];
		$countColumnAs = $countArray['counted_column_name'];
		$sql .= " COUNT($countColumn) $countColumnAs, $selectColumns";

		// >>>>>>>>>>>>>>>>
		// FROM
		$sql .= " FROM `$table`";

		// >>>>>>>>>>>>>>>>
		// GROUP BY
		/** @var array $groupBycolumnsArray */
		$groupBycolumnsArray = $options['group_by_columns'];
		$groupBycolumns = implode(", ", $groupBycolumnsArray);
		// append GROUPBY(s)
		$sql .= " GROUP BY {$groupBycolumns}";

		// >>>>>>>>>>>>>>>>
		// ORDER BY
		if (!empty($options['order_by_count_column'])) {
			$orderByCountColumn = $options['order_by_count_column'];
			// append ORDERBY COUNT
			$sql .= " ORDER BY COUNT({$orderByCountColumn})";
			if (!empty($options['order_by_descending'])) {
				$sql .= " DESC";
			}

		}

		// >>>>>>>>>>>>>>>>
		// LIMIT
		if (!empty($options['limit'])) {
			$limit = $options['limit'];
			// append LIMIT
			$sql .= " LIMIT {$limit}";
		}

		// >>>>>>>>>>>>>>>>
		// PREPARE QUERY
		$query = $database->prepare($sql);

		// >>>>>>>>>>>>>>>>
		// EXECUTE
		$query->execute();

		// >>>>>>>>>>>>>>>>
		// FETCH
		// fetch results
		$result = $query->fetchAll(\PDO::FETCH_ASSOC);

		// -----
		return $result;

	}

	/**
	 * Prepare and execute a PDO simple Select query.
	 *
	 * @param array $options
	 * @return mixed
	 */
	protected function processQuerySelect(array $options) {
		// e.g.
		// SELECT pages_id as discount_id, code, global_usage, limit_total
		// FROM field_pwcommerce_discount
		// WHERE global_usage >= limit_total
		// AND limit_total > 0

		$database = $this->wire('database');

		// >>>>>>>>>>>>>>>>
		// TABLE
		$tablePrefix = "field_";
		if (!empty($options['is_not_use_prefix'])) {
			$tablePrefix = "";
		}

		$table = $tablePrefix . $options['table'];
		$table = $database->escapeTable($table);

		// >>>>>>>>>>>>>>>>
		// SELECT

		// select column(s)
		/** @var array $selectColumnsArray */
		$selectColumnsArray = $options['select_columns'];
		$selectColumns = implode(", ", $selectColumnsArray);

		$sql = "SELECT $selectColumns FROM `$table`";

		// >>>>>>>>>>>>>>>>
		// WHERE & AND condition(s)
		if (!empty($options['conditions'])) {
			/** @var array $conditionsArray */
			$conditionsArray = $options['conditions'];
			$conditionString = "";
			foreach ($conditionsArray as $indexCount => $condition) {
				// $andConditionsString = "AND "
				$columnName = $condition['column_name'];
				$paramName = !empty($condition['param_identifier']) ? $condition['param_identifier'] : $condition['column_name'];
				$operator = $condition['operator'];
				$columnType = $condition['column_type'];

				// to bind or not
				if (!empty($condition['skip_bind'])) {
					$bind = '';
					$paramName = $condition['column_value'];
				} else {
					$bind = ":";
				}

				// -------
				if ($indexCount === 0) {
					// $conditionString .= "WHERE pages_id=:page_id "
					// $conditionString .= "WHERE {$columnName}{$operator}:{$paramName}";
					$conditionString .= "WHERE {$columnName}{$operator}{$bind}{$paramName}";
				} else {
					// $conditionString .= " AND {$columnName}{$operator}:{$paramName}";
					$conditionString .= " AND {$columnName}{$operator}{$bind}{$paramName}";
				}
			}

			// append CONDITION(s)
			$sql .= " {$conditionString}";
		}

		// >>>>>>>>>>>>>>>>
		// ORDER BY
		if (!empty($options['order_by_columns'])) {
			/** @var array $orderBycolumnsArray */
			$orderBycolumnsArray = $options['order_by_columns'];
			$orderBycolumns = implode(", ", $orderBycolumnsArray);
			if (!empty($options['order_by_descending'])) {
				$orderBycolumns .= " DESC";
			}
			// append ORDERBY(s)
			$sql .= " ORDER BY {$orderBycolumns}";

		}

		// >>>>>>>>>>>>>>>>
		// PREPARE QUERY
		$query = $database->prepare($sql);

		// >>>>>>>>>>>>>>>>
		// BIND QUERY PARAMS
		if (!empty($options['conditions'])) {
			foreach ($conditionsArray as $condition) {
				if (!empty($condition['skip_bind'])) {
					continue;
				}
				// ------
				$paramName = !empty($condition['param_identifier']) ? $condition['param_identifier'] : $condition['column_name'];
				$operator = $condition['operator'];
				$columnValue = $condition['column_value'];
				$columnType = $condition['column_type'];
				// ---------
				// bind our named parameter
				if ($columnType === 'int') {
					$query->bindValue(":{$paramName}", $columnValue, \PDO::PARAM_INT);
				} else {
					$query->bindValue(":{$paramName}", $columnValue, \PDO::PARAM_STR);
				}
			}
		}

		// >>>>>>>>>>>>>>>>
		// EXECUTE
		$query->execute();

		// >>>>>>>>>>>>>>>>
		// FETCH
		// fetch results
		$result = $query->fetchAll(\PDO::FETCH_ASSOC);

		// -----
		return $result;

	}

	/**
	 * Prepare and execute a PDO GroupBy, Sum and Having query.
	 *
	 * @param array $options
	 * @return mixed
	 */
	protected function processQueryGroupBySumHaving(array $options) {
		// TODO consider refactor as quite similar to processQueryGroupBySum

		$database = $this->wire('database');

		// >>>>>>>>>>>>>>>>
		// TABLE
		$tablePrefix = "field_";
		$table = $tablePrefix . $options['table'];
		$table = $database->escapeTable($table);

		// >>>>>>>>>>>>>>>>
		// SELECT
		// select column(s)
		/** @var array $selectColumnsArray */
		$selectColumnsArray = $options['select_columns'];
		$selectColumns = implode(", ", $selectColumnsArray);

		$sql = "SELECT $selectColumns";

		// >>>>>>>>>>>>>>>>
		// SUM
		$sumArray = $options['sum'];
		$sumExpression = $sumArray['expression'];
		$sumColumnAs = $sumArray['summed_column_name'];
		$sql .= ", SUM($sumExpression) $sumColumnAs";

		// >>>>>>>>>>>>>>>>
		// FROM
		$sql .= " FROM `$table`";

		// >>>>>>>>>>>>>>>>
		// WHERE & AND condition(s)
		if (!empty($options['conditions'])) {
			/** @var array $conditionsArray */
			$conditionsArray = $options['conditions'];
			$conditionString = "";
			foreach ($conditionsArray as $indexCount => $condition) {
				// $andConditionsString = "AND "
				$columnName = $condition['column_name'];
				$paramName = !empty($condition['param_identifier']) ? $condition['param_identifier'] : $condition['column_name'];
				$operator = $condition['operator'];
				$columnType = $condition['column_type'];

				// to bind or not
				if (!empty($condition['skip_bind'])) {
					$bind = '';
					$paramName = $condition['column_value'];
				} else {
					$bind = ":";
				}

				// -------
				if ($indexCount === 0) {
					// $conditionString .= "WHERE pages_id=:page_id "
					// $conditionString .= "WHERE {$columnName}{$operator}:{$paramName}";
					$conditionString .= "WHERE {$columnName}{$operator}{$bind}{$paramName}";
				} else {
					// $conditionString .= " AND {$columnName}{$operator}:{$paramName}";
					$conditionString .= " AND {$columnName}{$operator}{$bind}{$paramName}";
				}
			}

			// append CONDITION(s)
			$sql .= " {$conditionString}";

		}

		// >>>>>>>>>>>>>>>>
		// GROUP BY
		/** @var array $groupBycolumnsArray */
		$groupBycolumnsArray = $options['group_by_columns'];
		$groupBycolumns = implode(", ", $groupBycolumnsArray);
		// append GROUPBY(s)
		$sql .= " GROUP BY {$groupBycolumns}";

		// >>>>>>>>>>>>>>>>
		// HAVING
		$havingArray = $options['having'];
		$havingExpression = $havingArray['expression'];
		$sql .= " HAVING $havingExpression";

		// >>>>>>>>>>>>>>>>
		// ORDER BY
		if (!empty($options['order_by_columns'])) {
			/** @var array $orderBycolumnsArray */
			$orderBycolumnsArray = $options['order_by_columns'];
			$orderBycolumns = implode(", ", $orderBycolumnsArray);
			if (!empty($options['order_by_descending'])) {
				$orderBycolumns .= " DESC";
			}
			// append ORDERBY(s)
			$sql .= " ORDER BY {$orderBycolumns}";
		}

		// >>>>>>>>>>>>>>>>
		// LIMIT
		if (!empty($options['limit'])) {
			$limit = $options['limit'];
			// append LIMIT
			$sql .= " LIMIT {$limit}";
		}

		// >>>>>>>>>>>>>>>>
		// PREPARE QUERY
		$query = $database->prepare($sql);

		// >>>>>>>>>>>>>>>>
		// BIND QUERY PARAMS
		if (!empty($options['conditions'])) {
			foreach ($conditionsArray as $condition) {
				if (!empty($condition['skip_bind'])) {
					continue;
				}
				// ------
				$paramName = !empty($condition['param_identifier']) ? $condition['param_identifier'] : $condition['column_name'];
				$operator = $condition['operator'];
				$columnValue = $condition['column_value'];
				$columnType = $condition['column_type'];
				// ---------
				// bind our named parameter
				if ($columnType === 'int') {
					$query->bindValue(":{$paramName}", $columnValue, \PDO::PARAM_INT);
				} else {
					$query->bindValue(":{$paramName}", $columnValue, \PDO::PARAM_STR);
				}
			}
		}

		// >>>>>>>>>>>>>>>>
		// EXECUTE
		$query->execute();

		// >>>>>>>>>>>>>>>>
		// FETCH
		// fetch results
		$result = $query->fetchAll(\PDO::FETCH_ASSOC);

		// -----
		return $result;

	}

	/**
	 * Process Query Product Count In All Carts.
	 *
	 * @param int $productID
	 * @param int $intervalValue
	 * @param string $intervalType
	 * @return mixed
	 */
	public function processQueryProductCountInAllCarts(int $productID, int $intervalValue, string $intervalType) {
		// TODO NEED TO RETURN STDCLASS FOR CONSISTENCY!
		$queryResult = [
			'notice' => '',
			'notice_type' => 'error'
		];

		if (empty($productID)) {
			$notice = $this->_('Product ID cannot be empty');
			$queryResult['notice'] = $notice;
			// ----
			return $queryResult;
		}

		$intervalValue = !empty($intervalValue) ? $intervalValue : PwCommerce::MYSQL_DATE_FUNCTIONS_DEFAULT_INTERVAL;

		$intervalTypes = $this->getMySQLDateFunctionsIntervalTypes();

		$intervalType = $this->wire('sanitizer')->fieldName($intervalType);
		if (empty($intervalType) || empty($intervalTypes[$intervalType])) {
			// assign default interval type, i.e. 'DAY'
			$intervalType = PwCommerce::MYSQL_DATE_FUNCTIONS_DEFAULT_INTERVAL_TYPE;
		} else {
			$intervalType = $intervalTypes[$intervalType];
		}

		$countResult = $this->mySQLDateFunctionsBuildIntervalQuery($productID, $intervalValue, $intervalType);
		return $countResult;
		// TODO ERROR CHECK ABOVE?
		// $queryResult = [
		// 	'notice' => $countResult,
		// 	'notice_type' => 'success'
		// ];
		// return $queryResult;

	}

	/**
	 * My S Q L Date Functions Build Interval Query.
	 *
	 * @param int $productID
	 * @param int $intervalValue
	 * @param string $intervalType
	 * @return mixed
	 */
	private function mySQLDateFunctionsBuildIntervalQuery(int $productID, int $intervalValue, string $intervalType) {
		$sql = "
			SELECT SUM(quantity) AS count " .
			"FROM " . PwCommerce::PWCOMMERCE_CART_TABLE_NAME . " cart " .
			// NOTE DOES NOT WORK SINCE CANNOT BIND A SQL KEYEWORD. OK, since we only allow known intervalType!
			// "WHERE cart.last_modified >= DATE_SUB(NOW(), INTERVAL :interval_value :interval_type) " .
			"WHERE cart.last_modified >= DATE_SUB(NOW(), INTERVAL :interval_value $intervalType) " .
			"AND product_id = :product_id";

		// PREPARE QUERY
		$query = $this->database->prepare($sql);
		$query->bindParam(":product_id", $productID, \PDO::PARAM_INT);
		$query->bindParam(":interval_value", $intervalValue, \PDO::PARAM_INT);
		// $query->bindParam(":interval_type", $intervalType, \PDO::PARAM_STR);
		// $query->bindValue(":interval_type", $intervalType, \PDO::PARAM_STR);
		$query->execute();

		// return $query->fetchAll(\PDO::FETCH_CLASS, "ProcessWire\WireData");
		// return $query->fetch(\PDO::FETCH_CLASS, "ProcessWire\WireData");
		return $query->fetchObject("ProcessWire\WireData");
		// return $query->fetchAll(\PDO::FETCH_CLASS);
		// return $query->fetch();
	}

	/**
	 * Get My S Q L Date Functions Interval Types.
	 *
	 * @return mixed
	 */
	private function getMySQLDateFunctionsIntervalTypes() {
		$intervalTypes = [
			'microsecond' => 'MICROSECOND',
			'second' => 'SECOND',
			'minute' => 'MINUTE',
			'hour' => 'HOUR',
			'day' => 'DAY', // DEFAULT
			'week' => 'WEEK',
			'month' => 'MONTH',
			'quarter' => 'QUARTER',
			'year' => 'YEAR',
			'second_microsecond' => 'SECOND_MICROSECOND',
			'minute_microsecond' => 'MINUTE_MICROSECOND',
			'minute_second' => 'MINUTE_SECOND',
			'hour_microsecond' => 'HOUR_MICROSECOND',
			'hour_second' => 'HOUR_SECOND',
			'hour_minute' => 'HOUR_MINUTE',
			'day_microsecond' => 'DAY_MICROSECOND',
			'day_second' => 'DAY_SECOND',
			'day_minute' => 'DAY_MINUTE',
			'day_hour' => 'DAY_HOUR',
			'year_month' => 'YEAR_MONTH',
		];
		// -----
		return $intervalTypes;
	}

}
