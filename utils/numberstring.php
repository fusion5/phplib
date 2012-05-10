<?
	class numberstring {
		private
			$unit = array(
					"", "unu", "doi", "trei", "patru", "cinci", "şase", "şapte", "opt", "nouă"
			);
		private
			$unitf = array(
					"", "o", "două", "trei", "patru", "cinci", "şase", "şapte", "opt", "nouă"
			);
		private
			$unitn = array(
					"", "un", "două", "trei", "patru", "cinci", "şase", "şapte", "opt", "nouă"
			);
		private
			$firstdecimal = array(
				10 => "zece", 
				11 => "unsprezece", 
				12 => "doisprezece", 
				13 => "treisprezece", 
				14 => "patruprezece",
				15 => "cincisprezece",
				16 => "şaisprezece",
				17 => "şaptisprezece",
				18 => "optsprezece",
				19 => "nouăsprezece"  
			);
		private
			$decimal = array(
				"", "", "douăzeci", "treizeci", "patruzeci", "cincizeci", "şaizeci", "şaptezeci", "optzeci", "nouăzeci"
			);
		private
			$value; 
		public
			function __construct($nr) 
			{
				$nr = (int)$nr;
				if ($nr < 1000000)
					$this->value = $this->mod1000_000($nr);
				else
					$this->value = $nr;
			}		
		public
			function __toString()
			{
				return first($this->getValue(), 'Eroare - nici un numar nu a fost specificat');
			}
		public 
			function getValue()
			{
				return $this->value;
			}
		private
			function mod1000_000($nr) {
				$ret = '';
				if ($nr == 0):
					$ret = 'zero';
				elseif ((1 <= $nr)&&($nr <= 999)):
					$ret = $this->mod1000($nr);
				elseif ((1000 <= $nr) && ($nr <= 1999)):
					$ret = "o mie " . $this->mod1000($nr%1000);
				elseif ((2000 <= $nr) && ($nr <= 9999)):
					$ret = $this->unitf[(int)$nr/1000] . " mii ". $this->mod1000($nr % 1000);
				elseif ((10000 <= $nr) && ($nr < 1000000)):
					$ret = $this->mod1000((int)($nr/1000)) . " de mii ". $this->mod1000($nr % 1000);
				endif;
				return $ret;
			}
		private
			function mod1000($nr) {
				$ret = '';
				if ((1 <= $nr) && ($nr <= 99)):
					$ret = $this->mod100($nr);
				elseif ((100 <= $nr) && ($nr <= 199)):
					$nr_cent = $nr%100;
					$ret = "o sută " . $this->mod100($nr_cent);
				elseif ((200 <= $nr) && ($nr <= 999)):
					$m = (int)$nr/100;
					$ret = $this->unitf[$m] . " sute " . $this->mod100($nr%100);
				endif;
				return $ret;
			}
		private
			function mod100($nr) {
				$ret = '';
				if ((0 < $nr) && ($nr <= 9)):
						$ret = $this->unit[$nr];
				elseif ((10 <= $nr ) && ($nr <= 19)):
						$ret = $this->firstdecimal[$nr];
				elseif ((20 <= $nr ) && ($nr <= 99)):
				    $unit = $nr % 10;
				    $dec = (int)($nr / 10);
				    $ret = $this->decimal[$dec];
				    if ($unit != 0)
				    	$ret .= " şi " . $this->mod100($unit);
				    ;
				endif;
				return $ret;
			}
	}
?>
