<?php 
/**
 * 
 * @author Enrico Atzeni <enrico.atzeni@outlook.com>
 * @copyright 2019
 * @license MIT
 * @source 
 * 
 */
/**
*
*  @author    INTELLIJAM SRL <michele.silletti@intellijam.com>
*  @copyright 2018 INTELLIJAM SRL
*  @license   commercial use
*
*/
class CSSExtractor {
    /**
     * Separate string by $separate_by char, trim each values to remove
     * white spaces and filter using "strlen" function to remove
     * empty rows but (thanks to strlen) keeping rows with zero (0) value
     *
     * @param mixed $string
     * @param mixed $separate_by
     * @return void
     */
    private static function separateAndTrim($string, $separate_by)
	{
		// separate string
		$array = explode($separate_by, $string);
		// trim white spaces
		$array = array_map('trim', $array);
		// remove empty elemetns
		$array = array_filter($array, 'strlen');

		return $array;
	}

	/**
	 * Trim CSS comments from text
	 *
	 * @param string $string
	 * @return string
	 */
	private static function trimComments($string)
	{
		return preg_replace('#/\*.+?(?=\*/)\*/#ms', '', $string);
	}

	/**
	 * Extract all css rules from a CSS text
	 * Returns an Array of rules and values
     * 
	 * @param string $css
     * @param bool $merge True merges all rules using selectors, 
     *                      False returns all rules sequentially as read from $css
	 * @return array
	 */
	public static function extract($css, $merge = true)
	{
		$css = self::trimComments($css);

		$pattern = '/^([\.#\w][^\{]+)[^\{]*\{([^\}]+)+\}/ms';
		$match = [];

		preg_match_all($pattern, $css, $match);

		$all_rules = [];
		foreach ($match[1] as $index => $selector) {
			// shortcut naming
			$rule_block = $match[2][$index];

			// separate selectors by comma and trim them
			$selectors = self::separateAndTrim($selector, ',');

			// separate rules by semicolon and trim them
			$rules = self::separateAndTrim($rule_block, ';');

			// If no rules left skip
			if (empty($rules)) {
				continue;
			}

			// explode rules into key => value
			$separed_rules = self::explodeRules($rules);
			
			$all_rules[] = [
				'selectors' => $selectors,
				'rules' => $separed_rules
			];
        }
        
        // do we need to merge all rules into selectors?
        if ($merge) {
            // echo '<pre>';print_r($css_rules);exit;

            $selectors = [];

            foreach ($all_rules as $rule) {
                foreach ($rule['selectors'] as $selector) {
                    if (!isset($selectors[$selector])) {
                        $selectors[$selector] = [];
                    }
                    $selectors[$selector] = array_merge($selectors[$selector], $rule['rules']);
                }
            }

            return $selectors;
        }

		return $all_rules;
    }
    
    /**
     * Separate rules from format "key:value" to 
     * associative array as "key" => "value"
     *
     * @param array $rules
     * @return array
     */
    public static function explodeRules($rules)
    {
        $separed_rules = [];
        foreach ($rules as $rule) {
            $sep_rule = self::separateAndTrim($rule, ':');
            $separed_rules[$sep_rule[0]] = $sep_rule[1];
        }
        return $separed_rules;
    }

	/**
	 * Merge rules array from format "key" => "value" into
     * string inline format "key:value"
	 *
	 * @param array $rules
	 * @return string
	 */
	public static function inlineRules($rules)
	{
		$merged_rules = [];
		foreach ($rules as $rule => $value) {
			$merged_rules[] = "{$rule}:{$value}";
		}
		return implode(';', $merged_rules);
	}
}
