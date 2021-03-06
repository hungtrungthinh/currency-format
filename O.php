<?php
namespace Dfe\CurrencyFormat;
use Dfe\CurrencyFormat\FE;
final class O extends \Df\Config\ArrayItem {
	/** @return string */
	function code() {return $this->v();}
	/** @return string */
	function decimalSeparator() {return $this->v('.');}
	/** @return bool */
	function delimitSymbolFromAmount() {return $this->b();}

	/**
	 * 2015-12-31
	 * @override
	 * @see \Df\Config\ArrayItem::id()
	 * @used-by \Df\Config\A::get()
	 * @return string
	 */
	function id() {return $this->code();}

	/**
	 * 2016-08-01
	 * @used-by \Dfe\CurrencyFormat\Plugin\Directory\Model\Currency::aroundFormatTxt()
	 * @used-by \Dfe\CurrencyFormat\Observer\DisplayOptionsForming::execute()
	 * @return array(string => string|int|null)
	 */
	function options() {
		if (!isset($this->{__METHOD__})) {
			/** @var array(mixed => mixed) $result */
			$result = [];
			if (!$this->showDecimals()) {
				$result['precision'] = 0;
			}
			/** @var string $delimiter */
			$delimiter = !$this->delimitSymbolFromAmount() ? '' : DF_THIN_SPACE;
			/**
			 * 2015-12-31
			 * http://framework.zend.com/manual/1.12/en/zend.locale.parsing.html
			 */
			/** @var string[] $formatA */
			$formatA = ["#,##0.00", $delimiter, '¤'];
			if ('before' === $this->symbolPosition()) {
				$formatA = array_reverse($formatA);
				/**
				 * 2015-12-31
				 * Когда символ валюты надо отобразить слева от суммы,
				 * то по какой-то неведомой причине
				 * недостаточно указать позицию символа валюты в шаблоне (символом ¤),
				 * но также нужно явно указать @uses \Zend_Currency::LEFT значением параметра
				 * «position»,иначе символ валюты вообще не будет отображён.
				 * https://github.com/zendframework/zf1/blob/release-1.12.16/library/Zend/Currency.php#L196-L209
				 */
				$result['position'] = \Zend_Currency::LEFT;
			}
			// 2015-12-31
			// https://github.com/zendframework/zf1/blob/release-1.12.16/library/Zend/Currency.php#L182
			$result['format'] = implode($formatA);
			$this->{__METHOD__} = $result;
		}
		return $this->{__METHOD__};
	}

	/**
	 * 2016-08-01
	 * 2015-12-31
	 * Подменяем стандартные decimals and thousands separators на свои
	 * @used-by \Dfe\CurrencyFormat\Plugin\Directory\Model\Currency::aroundFormatTxt()
	 * @used-by \Dfe\CurrencyFormat\Plugin\Framework\Currency::afterToCurrency()
	 *
	 * 2016-08-01
	 * Этот метод нельзя вызывать повторно для одной и той же строки,
	 * иначе разделители (десятичный и тысячный) утрачиваются.
	 *
	 * @param string $priceS
	 * @param string|null $l [optional]
	 * @return string
	 */
	function postProcess($priceS, $l = null) {
		/** @var array(string => string) $symbols */
		$symbols = \Zend_Locale_Data::getList(df_locale($l), 'symbols');
		/** @var array(string => string) $map */
		$map = ['decimal' => $this->decimalSeparator(), 'group' => $this->thousandsSeparator()];
		$keys = array_keys($map); /** @var string[] $keys */
		return strtr(strtr($priceS, array_combine(dfa_select($symbols, $keys) + $map, $keys)), $map);
	}

	/**
	 * 2015-12-26
	 * «Mage2.PRO» → «Currency» → «Format» → «Number of Decimals»
	 * Zend Framework автоматически округляет цены до заданного количества десятичных знаков:
	 *	if (is_numeric($options['precision'])) {
	 *		$value = Zend_Locale_Math::round($value, $options['precision']);
	 *	}
	 * https://github.com/zendframework/zf1/blob/release-1.12.16/library/Zend/Locale/Format.php#L329
	 * @return bool
	 */
	function showDecimals() {return $this->b(true);}

	/** @return string */
	function symbolPosition() {return $this->v('before');}

	/**
	 * 2015-12-31
	 * @return string
	 */
	function thousandsSeparator() {
		/** @var string $result */
		$result = $this->v(FE::TS__NONE);
		return dfa([FE::TS__NONE => '', FE::TS__THIN_SPACE => DF_THIN_SPACE], $result, $result);
	}

	const code = 'code';
	const decimalSeparator = 'decimalSeparator';
	const delimitSymbolFromAmount = 'delimitSymbolFromAmount';
	const showDecimals = 'showDecimals';
	const symbolPosition = 'symbolPosition';
	const thousandsSeparator = 'thousandsSeparator';
}