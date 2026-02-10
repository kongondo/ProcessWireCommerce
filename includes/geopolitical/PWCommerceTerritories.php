<?php

namespace ProcessWire;

/**
 * PWCommerce: Territories
 *
 * Class to deal with Territories for PWCommerce general use.
 *
 * @author Francis Otieno (Kongondo) <kongondo@gmail.com> kongondo.com
 *
 *
 *
 * PWCommerceTerritories for PWCommerce
 * Copyright (C) 2025 by Francis Otieno
 * MIT License
 *
 */

class PWCommerceTerritories extends WireData
{

	/**
	 * Returns array of territories (states, provinces, cantons, etc) for various countries and their codes.
	 *
	 * @return mixed
	 */
	public function getTerritories() {

		$territories = [
			// Angola States
			[
				"country" => "AO",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "BGO",
						"name" => "Bengo",
					],
					[
						"code" => "BLU",
						"name" => "Benguela",
					],
					[
						"code" => "BIE",
						"name" => "Bié",
					],
					[
						"code" => "CAB",
						"name" => "Cabinda",
					],
					[
						"code" => "CNN",
						"name" => "Cunene",
					],
					[
						"code" => "HUA",
						"name" => "Huambo",
					],
					[
						"code" => "HUI",
						"name" => "Huíla",
					],
					[
						"code" => "CCU",
						"name" => "Kuando Kubango",
					],
					[
						"code" => "CNO",
						"name" => "Kwanza-Norte",
					],
					[
						"code" => "CUS",
						"name" => "Kwanza-Sul",
					],
					[
						"code" => "LUA",
						"name" => "Luanda",
					],
					[
						"code" => "LNO",
						"name" => "Lunda-Norte",
					],
					[
						"code" => "LSU",
						"name" => "Lunda-Sul",
					],
					[
						"code" => "MAL",
						"name" => "Malanje",
					],
					[
						"code" => "MOX",
						"name" => "Moxico",
					],
					[
						"code" => "NAM",
						"name" => "Namibe",
					],
					[
						"code" => "UIG",
						"name" => "Uíge",
					],
					[
						"code" => "ZAI",
						"name" => "Zaire",
					],
				],
			],
			// Argentinian Provinces
			[
				"country" => "AR",
				"territories_reference" => "Provinces",
				"territories" => [
					[
						"code" => "C",
						"name" => "Ciudad Autónoma de Buenos Aires",
					],
					[
						"code" => "B",
						"name" => "Buenos Aires",
					],
					[
						"code" => "K",
						"name" => "Catamarca",
					],
					[
						"code" => "H",
						"name" => "Chaco",
					],
					[
						"code" => "U",
						"name" => "Chubut",
					],
					[
						"code" => "X",
						"name" => "Córdoba",
					],
					[
						"code" => "W",
						"name" => "Corrientes",
					],
					[
						"code" => "E",
						"name" => "Entre Ríos",
					],
					[
						"code" => "P",
						"name" => "Formosa",
					],
					[
						"code" => "Y",
						"name" => "Jujuy",
					],
					[
						"code" => "L",
						"name" => "La Pampa",
					],
					[
						"code" => "F",
						"name" => "La Rioja",
					],
					[
						"code" => "M",
						"name" => "Mendoza",
					],
					[
						"code" => "N",
						"name" => "Misiones",
					],
					[
						"code" => "Q",
						"name" => "Neuquén",
					],
					[
						"code" => "R",
						"name" => "Río Negro",
					],
					[
						"code" => "A",
						"name" => "Salta",
					],
					[
						"code" => "J",
						"name" => "San Juan",
					],
					[
						"code" => "D",
						"name" => "San Luis",
					],
					[
						"code" => "Z",
						"name" => "Santa Cruz",
					],
					[
						"code" => "S",
						"name" => "Santa Fe",
					],
					[
						"code" => "G",
						"name" => "Santiago del Estero",
					],
					[
						"code" => "V",
						"name" => "Tierra del Fuego",
					],
					[
						"code" => "T",
						"name" => "Tucumán",
					],
				],
			],
			// Australian States
			[
				"country" => "AU",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "ACT",
						"name" => "Australian Capital Territory",
					],
					[
						"code" => "NSW",
						"name" => "New South Wales",
					],
					[
						"code" => "NT",
						"name" => "Northern Territory",
					],
					[
						"code" => "QLD",
						"name" => "Queensland",
					],
					[
						"code" => "SA",
						"name" => "South Australia",
					],
					[
						"code" => "TAS",
						"name" => "Tasmania",
					],
					[
						"code" => "VIC",
						"name" => "Victoria",
					],
					[
						"code" => "WA",
						"name" => "Western Australia",
					],
				],
			],
			// Bangladeshi States (Districts)
			[
				"country" => "BD",
				"territories_reference" => "Districts",
				"territories" => [
					[
						"code" => "BD-05",
						"name" => "Bagerhat",
					],
					[
						"code" => "BD-01",
						"name" => "Bandarban",
					],
					[
						"code" => "BD-02",
						"name" => "Barguna",
					],
					[
						"code" => "BD-06",
						"name" => "Barishal",
					],
					[
						"code" => "BD-07",
						"name" => "Bhola",
					],
					[
						"code" => "BD-03",
						"name" => "Bogura",
					],
					[
						"code" => "BD-04",
						"name" => "Brahmanbaria",
					],
					[
						"code" => "BD-09",
						"name" => "Chandpur",
					],
					[
						"code" => "BD-10",
						"name" => "Chattogram",
					],
					[
						"code" => "BD-12",
						"name" => "Chuadanga",
					],
					[
						"code" => "BD-11",
						"name" => "Cox's Bazar",
					],
					[
						"code" => "BD-08",
						"name" => "Cumilla",
					],
					[
						"code" => "BD-13",
						"name" => "Dhaka",
					],
					[
						"code" => "BD-14",
						"name" => "Dinajpur",
					],
					[
						"code" => "BD-15",
						"name" => "Faridpur ",
					],
					[
						"code" => "BD-16",
						"name" => "Feni",
					],
					[
						"code" => "BD-19",
						"name" => "Gaibandha",
					],
					[
						"code" => "BD-18",
						"name" => "Gazipur",
					],
					[
						"code" => "BD-17",
						"name" => "Gopalganj",
					],
					[
						"code" => "BD-20",
						"name" => "Habiganj",
					],
					[
						"code" => "BD-21",
						"name" => "Jamalpur",
					],
					[
						"code" => "BD-22",
						"name" => "Jashore",
					],
					[
						"code" => "BD-25",
						"name" => "Jhalokati",
					],
					[
						"code" => "BD-23",
						"name" => "Jhenaidah",
					],
					[
						"code" => "BD-24",
						"name" => "Joypurhat",
					],
					[
						"code" => "BD-29",
						"name" => "Khagrachhari",
					],
					[
						"code" => "BD-27",
						"name" => "Khulna",
					],
					[
						"code" => "BD-26",
						"name" => "Kishoreganj",
					],
					[
						"code" => "BD-28",
						"name" => "Kurigram",
					],
					[
						"code" => "BD-30",
						"name" => "Kushtia",
					],
					[
						"code" => "BD-31",
						"name" => "Lakshmipur",
					],
					[
						"code" => "BD-32",
						"name" => "Lalmonirhat",
					],
					[
						"code" => "BD-36",
						"name" => "Madaripur",
					],
					[
						"code" => "BD-37",
						"name" => "Magura",
					],
					[
						"code" => "BD-33",
						"name" => "Manikganj ",
					],
					[
						"code" => "BD-39",
						"name" => "Meherpur",
					],
					[
						"code" => "BD-38",
						"name" => "Moulvibazar",
					],
					[
						"code" => "BD-35",
						"name" => "Munshiganj",
					],
					[
						"code" => "BD-34",
						"name" => "Mymensingh",
					],
					[
						"code" => "BD-48",
						"name" => "Naogaon",
					],
					[
						"code" => "BD-43",
						"name" => "Narail",
					],
					[
						"code" => "BD-40",
						"name" => "Narayanganj",
					],
					[
						"code" => "BD-42",
						"name" => "Narsingdi",
					],
					[
						"code" => "BD-44",
						"name" => "Natore",
					],
					[
						"code" => "BD-45",
						"name" => "Nawabganj",
					],
					[
						"code" => "BD-41",
						"name" => "Netrakona",
					],
					[
						"code" => "BD-46",
						"name" => "Nilphamari",
					],
					[
						"code" => "BD-47",
						"name" => "Noakhali",
					],
					[
						"code" => "BD-49",
						"name" => "Pabna",
					],
					[
						"code" => "BD-52",
						"name" => "Panchagarh",
					],
					[
						"code" => "BD-51",
						"name" => "Patuakhali",
					],
					[
						"code" => "BD-50",
						"name" => "Pirojpur",
					],
					[
						"code" => "BD-53",
						"name" => "Rajbari",
					],
					[
						"code" => "BD-54",
						"name" => "Rajshahi",
					],
					[
						"code" => "BD-56",
						"name" => "Rangamati",
					],
					[
						"code" => "BD-55",
						"name" => "Rangpur",
					],
					[
						"code" => "BD-58",
						"name" => "Satkhira",
					],
					[
						"code" => "BD-62",
						"name" => "Shariatpur",
					],
					[
						"code" => "BD-57",
						"name" => "Sherpur",
					],
					[
						"code" => "BD-59",
						"name" => "Sirajganj",
					],
					[
						"code" => "BD-61",
						"name" => "Sunamganj",
					],
					[
						"code" => "BD-60",
						"name" => "Sylhet",
					],
					[
						"code" => "BD-63",
						"name" => "Tangail",
					],
					[
						"code" => "BD-64",
						"name" => "Thakurgaon",
					],
				],
			],
			// Bulgarian States
			[
				"country" => "BG",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "BG-01",
						"name" => "Blagoevgrad",
					],
					[
						"code" => "BG-02",
						"name" => "Burgas",
					],
					[
						"code" => "BG-08",
						"name" => "Dobrich",
					],
					[
						"code" => "BG-07",
						"name" => "Gabrovo",
					],
					[
						"code" => "BG-26",
						"name" => "Haskovo",
					],
					[
						"code" => "BG-09",
						"name" => "Kardzhali",
					],
					[
						"code" => "BG-10",
						"name" => "Kyustendil",
					],
					[
						"code" => "BG-11",
						"name" => "Lovech",
					],
					[
						"code" => "BG-12",
						"name" => "Montana",
					],
					[
						"code" => "BG-13",
						"name" => "Pazardzhik",
					],
					[
						"code" => "BG-14",
						"name" => "Pernik",
					],
					[
						"code" => "BG-15",
						"name" => "Pleven",
					],
					[
						"code" => "BG-16",
						"name" => "Plovdiv",
					],
					[
						"code" => "BG-17",
						"name" => "Razgrad",
					],
					[
						"code" => "BG-18",
						"name" => "Ruse",
					],
					[
						"code" => "BG-27",
						"name" => "Shumen",
					],
					[
						"code" => "BG-19",
						"name" => "Silistra",
					],
					[
						"code" => "BG-20",
						"name" => "Sliven",
					],
					[
						"code" => "BG-21",
						"name" => "Smolyan",
					],
					[
						"code" => "BG-23",
						"name" => "Sofia",
					],
					[
						"code" => "BG-22",
						"name" => "Sofia-Grad",
					],
					[
						"code" => "BG-24",
						"name" => "Stara Zagora",
					],
					[
						"code" => "BG-25",
						"name" => "Targovishte",
					],
					[
						"code" => "BG-03",
						"name" => "Varna",
					],
					[
						"code" => "BG-04",
						"name" => "Veliko Tarnovo",
					],
					[
						"code" => "BG-05",
						"name" => "Vidin",
					],
					[
						"code" => "BG-06",
						"name" => "Vratsa",
					],
					[
						"code" => "BG-28",
						"name" => "Yambol",
					],
				],
			],
			// Bolivian States
			[
				"country" => "BO",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "B",
						"name" => "Chuquisaca",
					],
					[
						"code" => "H",
						"name" => "Beni",
					],
					[
						"code" => "C",
						"name" => "Cochabamba",
					],
					[
						"code" => "L",
						"name" => "La Paz",
					],
					[
						"code" => "O",
						"name" => "Oruro",
					],
					[
						"code" => "N",
						"name" => "Pando",
					],
					[
						"code" => "P",
						"name" => "Potosí",
					],
					[
						"code" => "S",
						"name" => "Santa Cruz",
					],
					[
						"code" => "T",
						"name" => "Tarija",
					],
				],
			],
			// Brazillian States
			[
				"country" => "BR",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "AC",
						"name" => "Acre",
					],
					[
						"code" => "AL",
						"name" => "Alagoas",
					],
					[
						"code" => "AP",
						"name" => "Amapá",
					],
					[
						"code" => "AM",
						"name" => "Amazonas",
					],
					[
						"code" => "BA",
						"name" => "Bahia",
					],
					[
						"code" => "CE",
						"name" => "Ceará",
					],
					[
						"code" => "DF",
						"name" => "Distrito Federal",
					],
					[
						"code" => "ES",
						"name" => "Espírito Santo",
					],
					[
						"code" => "GO",
						"name" => "Goiás",
					],
					[
						"code" => "MA",
						"name" => "Maranhão",
					],
					[
						"code" => "MT",
						"name" => "Mato Grosso",
					],
					[
						"code" => "MS",
						"name" => "Mato Grosso do Sul",
					],
					[
						"code" => "MG",
						"name" => "Minas Gerais",
					],
					[
						"code" => "PA",
						"name" => "Pará",
					],
					[
						"code" => "PB",
						"name" => "Paraíba",
					],
					[
						"code" => "PR",
						"name" => "Paraná",
					],
					[
						"code" => "PE",
						"name" => "Pernambuco",
					],
					[
						"code" => "PI",
						"name" => "Piauí",
					],
					[
						"code" => "RJ",
						"name" => "Rio de Janeiro",
					],
					[
						"code" => "RN",
						"name" => "Rio Grande do Norte",
					],
					[
						"code" => "RS",
						"name" => "Rio Grande do Sul",
					],
					[
						"code" => "RO",
						"name" => "Rondônia",
					],
					[
						"code" => "RR",
						"name" => "Roraima",
					],
					[
						"code" => "SC",
						"name" => "Santa Catarina",
					],
					[
						"code" => "SP",
						"name" => "São Paulo",
					],
					[
						"code" => "SE",
						"name" => "Sergipe",
					],
					[
						"code" => "TO",
						"name" => "Tocantins",
					],
				],
			],
			// Canadian Provinces
			[
				"country" => "CA",
				"territories_reference" => "Provinces",
				"territories" => [
					[
						"code" => "AB",
						"name" => "Alberta",
					],
					[
						"code" => "BC",
						"name" => "British Columbia",
					],
					[
						"code" => "MB",
						"name" => "Manitoba",
					],
					[
						"code" => "NB",
						"name" => "New Brunswick",
					],
					[
						"code" => "NL",
						"name" => "Newfoundland and Labrador",
					],
					[
						"code" => "NT",
						"name" => "Northwest Territories",
					],
					[
						"code" => "NS",
						"name" => "Nova Scotia",
					],
					[
						"code" => "NU",
						"name" => "Nunavut",
					],
					[
						"code" => "ON",
						"name" => "Ontario",
					],
					[
						"code" => "PE",
						"name" => "Prince Edward Island",
					],
					[
						"code" => "QC",
						"name" => "Quebec",
					],
					[
						"code" => "SK",
						"name" => "Saskatchewan",
					],
					[
						"code" => "YT",
						"name" => "Yukon Territory",
					],
				],
			],
			// Cantons of Switzerland
			[
				"country" => "CH",
				"territories_reference" => "Cantons",
				"territories" => [
					[
						"code" => "AG",
						"name" => "Aargau",
					],
					[
						"code" => "AR",
						"name" => "Appenzell Ausserrhoden",
					],
					[
						"code" => "AI",
						"name" => "Appenzell Innerrhoden",
					],
					[
						"code" => "BL",
						"name" => "Basel-Landschaft",
					],
					[
						"code" => "BS",
						"name" => "Basel-Stadt",
					],
					[
						"code" => "BE",
						"name" => "Bern",
					],
					[
						"code" => "FR",
						"name" => "Fribourg",
					],
					[
						"code" => "GE",
						"name" => "Geneva",
					],
					[
						"code" => "GL",
						"name" => "Glarus",
					],
					[
						"code" => "GR",
						"name" => "Graubünden",
					],
					[
						"code" => "JU",
						"name" => "Jura",
					],
					[
						"code" => "LU",
						"name" => "Luzern",
					],
					[
						"code" => "NE",
						"name" => "Neuchâtel",
					],
					[
						"code" => "NW",
						"name" => "Nidwalden",
					],
					[
						"code" => "OW",
						"name" => "Obwalden",
					],
					[
						"code" => "SH",
						"name" => "Schaffhausen",
					],
					[
						"code" => "SZ",
						"name" => "Schwyz",
					],
					[
						"code" => "SO",
						"name" => "Solothurn",
					],
					[
						"code" => "SG",
						"name" => "St. Gallen",
					],
					[
						"code" => "TG",
						"name" => "Thurgau",
					],
					[
						"code" => "TI",
						"name" => "Ticino",
					],
					[
						"code" => "UR",
						"name" => "Uri",
					],
					[
						"code" => "VS",
						"name" => "Valais",
					],
					[
						"code" => "VD",
						"name" => "Vaud",
					],
					[
						"code" => "ZG",
						"name" => "Zug",
					],
					[
						"code" => "ZH",
						"name" => "Zürich",
					],
				],
			],
			// Chinese States
			//   TODO: sort out the CODES after / !
			[
				"country" => "CN",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "CN1",
						"name" => "Yunnan",
					],
					[
						"code" => "CN2",
						"name" => "Beijing",
					],
					[
						"code" => "CN3",
						"name" => "Tianjin",
					],
					[
						"code" => "CN4",
						"name" => "Hebei",
					],
					[
						"code" => "CN5",
						"name" => "Shanxi",
					],
					[
						"code" => "CN6",
						"name" => "Inner Mongolia",
					],
					[
						"code" => "CN7",
						"name" => "Liaoning",
					],
					[
						"code" => "CN8",
						"name" => "Jilin",
					],
					[
						"code" => "CN9",
						"name" => "Heilongjiang",
					],
					[
						"code" => "CN10",
						"name" => "Shanghai",
					],
					[
						"code" => "CN11",
						"name" => "Jiangsu",
					],
					[
						"code" => "CN12",
						"name" => "Zhejiang",
					],
					[
						"code" => "CN13",
						"name" => "Anhui",
					],
					[
						"code" => "CN14",
						"name" => "Fujian",
					],
					[
						"code" => "CN15",
						"name" => "Jiangxi",
					],
					[
						"code" => "CN16",
						"name" => "Shandong",
					],
					[
						"code" => "CN17",
						"name" => "Henan",
					],
					[
						"code" => "CN18",
						"name" => "Hubei",
					],
					[
						"code" => "CN19",
						"name" => "Hunan",
					],
					[
						"code" => "CN20",
						"name" => "Guangdong",
					],
					[
						"code" => "CN21",
						"name" => "Guangxi Zhuang",
					],
					[
						"code" => "CN22",
						"name" => "Hainan",
					],
					[
						"code" => "CN23",
						"name" => "Chongqing",
					],
					[
						"code" => "CN24",
						"name" => "Sichuan",
					],
					[
						"code" => "CN25",
						"name" => "Guizhou",
					],
					[
						"code" => "CN26",
						"name" => "Shaanxi",
					],
					[
						"code" => "CN27",
						"name" => "Gansu",
					],
					[
						"code" => "CN28",
						"name" => "Qinghai",
					],
					[
						"code" => "CN29",
						"name" => "Ningxia Hui",
					],
					[
						"code" => "CN30",
						"name" => "Macau",
					],
					[
						"code" => "CN31",
						"name" => "Tibet",
					],
					[
						"code" => "CN32",
						"name" => "Xinjiang",
					],
				],
			],
			// Spain States
			[
				"country" => "ES",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "C",
						"name" => "A Coruña",
					],
					[
						"code" => "VI",
						"name" => "Araba/Álava",
					],
					[
						"code" => "AB",
						"name" => "Albacete",
					],
					[
						"code" => "A",
						"name" => "Alicante",
					],
					[
						"code" => "AL",
						"name" => "Almería",
					],
					[
						"code" => "O",
						"name" => "Asturias",
					],
					[
						"code" => "AV",
						"name" => "Ávila",
					],
					[
						"code" => "BA",
						"name" => "Badajoz",
					],
					[
						"code" => "PM",
						"name" => "Baleares",
					],
					[
						"code" => "B",
						"name" => "Barcelona",
					],
					[
						"code" => "BU",
						"name" => "Burgos",
					],
					[
						"code" => "CC",
						"name" => "Cáceres",
					],
					[
						"code" => "CA",
						"name" => "Cádiz",
					],
					[
						"code" => "S",
						"name" => "Cantabria",
					],
					[
						"code" => "CS",
						"name" => "Castellón",
					],
					[
						"code" => "CE",
						"name" => "Ceuta",
					],
					[
						"code" => "CR",
						"name" => "Ciudad Real",
					],
					[
						"code" => "CO",
						"name" => "Córdoba",
					],
					[
						"code" => "CU",
						"name" => "Cuenca",
					],
					[
						"code" => "GI",
						"name" => "Girona",
					],
					[
						"code" => "GR",
						"name" => "Granada",
					],
					[
						"code" => "GU",
						"name" => "Guadalajara",
					],
					[
						"code" => "SS",
						"name" => "Gipuzkoa",
					],
					[
						"code" => "H",
						"name" => "Huelva",
					],
					[
						"code" => "HU",
						"name" => "Huesca",
					],
					[
						"code" => "J",
						"name" => "Jaén",
					],
					[
						"code" => "LO",
						"name" => "La Rioja",
					],
					[
						"code" => "GC",
						"name" => "Las Palmas",
					],
					[
						"code" => "LE",
						"name" => "León",
					],
					[
						"code" => "L",
						"name" => "Lleida",
					],
					[
						"code" => "LU",
						"name" => "Lugo",
					],
					[
						"code" => "M",
						"name" => "Madrid",
					],
					[
						"code" => "MA",
						"name" => "Málaga",
					],
					[
						"code" => "ML",
						"name" => "Melilla",
					],
					[
						"code" => "MU",
						"name" => "Murcia",
					],
					[
						"code" => "NA",
						"name" => "Navarra",
					],
					[
						"code" => "OR",
						"name" => "Ourense",
					],
					[
						"code" => "P",
						"name" => "Palencia",
					],
					[
						"code" => "PO",
						"name" => "Pontevedra",
					],
					[
						"code" => "SA",
						"name" => "Salamanca",
					],
					[
						"code" => "TF",
						"name" => "Santa Cruz de Tenerife",
					],
					[
						"code" => "SG",
						"name" => "Segovia",
					],
					[
						"code" => "SE",
						"name" => "Sevilla",
					],
					[
						"code" => "SO",
						"name" => "Soria",
					],
					[
						"code" => "T",
						"name" => "Tarragona",
					],
					[
						"code" => "TE",
						"name" => "Teruel",
					],
					[
						"code" => "TO",
						"name" => "Toledo",
					],
					[
						"code" => "V",
						"name" => "Valencia",
					],
					[
						"code" => "VA",
						"name" => "Valladolid",
					],
					[
						"code" => "BI",
						"name" => "Bizkaia",
					],
					[
						"code" => "ZA",
						"name" => "Zamora",
					],
					[
						"code" => "Z",
						"name" => "Zaragoza",
					],
				],
			],
			// Greek Regions
			[
				"country" => "GR",
				"territories_reference" => "Regions",
				"territories" => [
					[
						"code" => "I",
						"name" => "Αττική",
					],
					[
						"code" => "A",
						"name" => "Ανατολική Μακεδονία και Θράκη",
					],
					[
						"code" => "B",
						"name" => "Κεντρική Μακεδονία",
					],
					[
						"code" => "C",
						"name" => "Δυτική Μακεδονία",
					],
					[
						"code" => "D",
						"name" => "Ήπειρος",
					],
					[
						"code" => "E",
						"name" => "Θεσσαλία",
					],
					[
						"code" => "F",
						"name" => "Ιόνιοι Νήσοι",
					],
					[
						"code" => "G",
						"name" => "Δυτική Ελλάδα",
					],
					[
						"code" => "H",
						"name" => "Στερεά Ελλάδα",
					],
					[
						"code" => "J",
						"name" => "Πελοπόννησος",
					],
					[
						"code" => "K",
						"name" => "Βόρειο Αιγαίο",
					],
					[
						"code" => "L",
						"name" => "Νότιο Αιγαίο",
					],
					[
						"code" => "M",
						"name" => "Κρήτη",
					],
				],
			],
			// Hong Kong States
			[
				"country" => "HK",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "HONG KONG",
						"name" => "Hong Kong Island",
					],
					[
						"code" => "KOWLOON",
						"name" => "Kowloon",
					],
					[
						"code" => "NEW TERRITORIES",
						"name" => "New Territories",
					],
				],
			],
			// Hungary States
			[
				"country" => "HU",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "BK",
						"name" => "Bács-Kiskun",
					],
					[
						"code" => "BE",
						"name" => "Békés",
					],
					[
						"code" => "BA",
						"name" => "Baranya",
					],
					[
						"code" => "BZ",
						"name" => "Borsod-Abaúj-Zemplén",
					],
					[
						"code" => "BU",
						"name" => "Budapest",
					],
					[
						"code" => "CS",
						"name" => "Csongrád",
					],
					[
						"code" => "FE",
						"name" => "Fejér",
					],
					[
						"code" => "GS",
						"name" => "Győr-Moson-Sopron",
					],
					[
						"code" => "HB",
						"name" => "Hajdú-Bihar",
					],
					[
						"code" => "HE",
						"name" => "Heves",
					],
					[
						"code" => "JN",
						"name" => "Jász-Nagykun-Szolnok",
					],
					[
						"code" => "KE",
						"name" => "Komárom-Esztergom",
					],
					[
						"code" => "NO",
						"name" => "Nógrád",
					],
					[
						"code" => "PE",
						"name" => "Pest",
					],
					[
						"code" => "SO",
						"name" => "Somogy",
					],
					[
						"code" => "SZ",
						"name" => "Szabolcs-Szatmár-Bereg",
					],
					[
						"code" => "TO",
						"name" => "Tolna",
					],
					[
						"code" => "VA",
						"name" => "Vas",
					],
					[
						"code" => "VE",
						"name" => "Veszprém",
					],
					[
						"code" => "ZA",
						"name" => "Zala",
					],
				],
			],
			// Indonesia Provinces
			[
				"country" => "ID",
				"territories_reference" => "Provinces",
				"territories" => [
					[
						"code" => "AC",
						"name" => "Daerah Istimewa Aceh",
					],
					[
						"code" => "SU",
						"name" => "Sumatera Utara",
					],
					[
						"code" => "SB",
						"name" => "Sumatera Barat",
					],
					[
						"code" => "RI",
						"name" => "Riau",
					],
					[
						"code" => "KR",
						"name" => "Kepulauan Riau",
					],
					[
						"code" => "JA",
						"name" => "Jambi",
					],
					[
						"code" => "SS",
						"name" => "Sumatera Selatan",
					],
					[
						"code" => "BB",
						"name" => "Bangka Belitung",
					],
					[
						"code" => "BE",
						"name" => "Bengkulu",
					],
					[
						"code" => "LA",
						"name" => "Lampung",
					],
					[
						"code" => "JK",
						"name" => "DKI Jakarta",
					],
					[
						"code" => "JB",
						"name" => "Jawa Barat",
					],
					[
						"code" => "BT",
						"name" => "Banten",
					],
					[
						"code" => "JT",
						"name" => "Jawa Tengah",
					],
					[
						"code" => "JI",
						"name" => "Jawa Timur",
					],
					[
						"code" => "YO",
						"name" => "Daerah Istimewa Yogyakarta",
					],
					[
						"code" => "BA",
						"name" => "Bali",
					],
					[
						"code" => "NB",
						"name" => "Nusa Tenggara Barat",
					],
					[
						"code" => "NT",
						"name" => "Nusa Tenggara Timur",
					],
					[
						"code" => "KB",
						"name" => "Kalimantan Barat",
					],
					[
						"code" => "KT",
						"name" => "Kalimantan Tengah",
					],
					[
						"code" => "KI",
						"name" => "Kalimantan Timur",
					],
					[
						"code" => "KS",
						"name" => "Kalimantan Selatan",
					],
					[
						"code" => "KU",
						"name" => "Kalimantan Utara",
					],
					[
						"code" => "SA",
						"name" => "Sulawesi Utara",
					],
					[
						"code" => "ST",
						"name" => "Sulawesi Tengah",
					],
					[
						"code" => "SG",
						"name" => "Sulawesi Tenggara",
					],
					[
						"code" => "SR",
						"name" => "Sulawesi Barat",
					],
					[
						"code" => "SN",
						"name" => "Sulawesi Selatan",
					],
					[
						"code" => "GO",
						"name" => "Gorontalo",
					],
					[
						"code" => "MA",
						"name" => "Maluku",
					],
					[
						"code" => "MU",
						"name" => "Maluku Utara",
					],
					[
						"code" => "PA",
						"name" => "Papua",
					],
					[
						"code" => "PB",
						"name" => "Papua Barat",
					],
				],
			],
			// Republic of Ireland
			[
				"country" => "IE",
				"territories_reference" => "",
				"territories" => [
					[
						"code" => "CW",
						"name" => "Carlow",
					],
					[
						"code" => "CN",
						"name" => "Cavan",
					],
					[
						"code" => "CE",
						"name" => "Clare",
					],
					[
						"code" => "CO",
						"name" => "Cork",
					],
					[
						"code" => "DL",
						"name" => "Donegal",
					],
					[
						"code" => "D",
						"name" => "Dublin",
					],
					[
						"code" => "G",
						"name" => "Galway",
					],
					[
						"code" => "KY",
						"name" => "Kerry",
					],
					[
						"code" => "KE",
						"name" => "Kildare",
					],
					[
						"code" => "KK",
						"name" => "Kilkenny",
					],
					[
						"code" => "LS",
						"name" => "Laois",
					],
					[
						"code" => "LM",
						"name" => "Leitrim",
					],
					[
						"code" => "LK",
						"name" => "Limerick",
					],
					[
						"code" => "LD",
						"name" => "Longford",
					],
					[
						"code" => "LH",
						"name" => "Louth",
					],
					[
						"code" => "MO",
						"name" => "Mayo",
					],
					[
						"code" => "MH",
						"name" => "Meath",
					],
					[
						"code" => "MN",
						"name" => "Monaghan",
					],
					[
						"code" => "OY",
						"name" => "Offaly",
					],
					[
						"code" => "RN",
						"name" => "Roscommon",
					],
					[
						"code" => "SO",
						"name" => "Sligo",
					],
					[
						"code" => "TA",
						"name" => "Tipperary",
					],
					[
						"code" => "WD",
						"name" => "Waterford",
					],
					[
						"code" => "WH",
						"name" => "Westmeath",
					],
					[
						"code" => "WX",
						"name" => "Wexford",
					],
					[
						"code" => "WW",
						"name" => "Wicklow",
					],
				],
			],
			// Indian States
			[
				"country" => "IN",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "AP",
						"name" => "Andhra Pradesh",
					],
					[
						"code" => "AR",
						"name" => "Arunachal Pradesh",
					],
					[
						"code" => "AS",
						"name" => "Assam",
					],
					[
						"code" => "BR",
						"name" => "Bihar",
					],
					[
						"code" => "CT",
						"name" => "Chhattisgarh",
					],
					[
						"code" => "GA",
						"name" => "Goa",
					],
					[
						"code" => "GJ",
						"name" => "Gujarat",
					],
					[
						"code" => "HR",
						"name" => "Haryana",
					],
					[
						"code" => "HP",
						"name" => "Himachal Pradesh",
					],
					[
						"code" => "JK",
						"name" => "Jammu and Kashmir",
					],
					[
						"code" => "JH",
						"name" => "Jharkhand",
					],
					[
						"code" => "KA",
						"name" => "Karnataka",
					],
					[
						"code" => "KL",
						"name" => "Kerala",
					],
					[
						"code" => "MP",
						"name" => "Madhya Pradesh",
					],
					[
						"code" => "MH",
						"name" => "Maharashtra",
					],
					[
						"code" => "MN",
						"name" => "Manipur",
					],
					[
						"code" => "ML",
						"name" => "Meghalaya",
					],
					[
						"code" => "MZ",
						"name" => "Mizoram",
					],
					[
						"code" => "NL",
						"name" => "Nagaland",
					],
					[
						"code" => "OR",
						"name" => "Orissa",
					],
					[
						"code" => "PB",
						"name" => "Punjab",
					],
					[
						"code" => "RJ",
						"name" => "Rajasthan",
					],
					[
						"code" => "SK",
						"name" => "Sikkim",
					],
					[
						"code" => "TN",
						"name" => "Tamil Nadu",
					],
					[
						"code" => "TS",
						"name" => "Telangana",
					],
					[
						"code" => "TR",
						"name" => "Tripura",
					],
					[
						"code" => "UK",
						"name" => "Uttarakhand",
					],
					[
						"code" => "UP",
						"name" => "Uttar Pradesh",
					],
					[
						"code" => "WB",
						"name" => "West Bengal",
					],
					[
						"code" => "AN",
						"name" => "Andaman and Nicobar Islands",
					],
					[
						"code" => "CH",
						"name" => "Chandigarh",
					],
					[
						"code" => "DN",
						"name" => "Dadra and Nagar Haveli",
					],
					[
						"code" => "DD",
						"name" => "Daman and Diu",
					],
					[
						"code" => "DL",
						"name" => "Delhi",
					],
					[
						"code" => "LD",
						"name" => "Lakshadeep",
					],
					[
						"code" => "PY",
						"name" => "Pondicherry (Puducherry)",
					],
				],
			],
			// Iran States
			[
				"country" => "IR",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "KHZ",
						"name" => "Khuzestan (خوزستان)",
					],
					[
						"code" => "THR",
						"name" => "Tehran (تهران)",
					],
					[
						"code" => "ILM",
						"name" => "Ilaam (ایلام)",
					],
					[
						"code" => "BHR",
						"name" => "Bushehr (بوشهر)",
					],
					[
						"code" => "ADL",
						"name" => "Ardabil (اردبیل)",
					],
					[
						"code" => "ESF",
						"name" => "Isfahan (اصفهان)",
					],
					[
						"code" => "YZD",
						"name" => "Yazd (یزد)",
					],
					[
						"code" => "KRH",
						"name" => "Kermanshah (کرمانشاه)",
					],
					[
						"code" => "KRN",
						"name" => "Kerman (کرمان)",
					],
					[
						"code" => "HDN",
						"name" => "Hamadan (همدان)",
					],
					[
						"code" => "GZN",
						"name" => "Ghazvin (قزوین)",
					],
					[
						"code" => "ZJN",
						"name" => "Zanjan (زنجان)",
					],
					[
						"code" => "LRS",
						"name" => "Luristan (لرستان)",
					],
					[
						"code" => "ABZ",
						"name" => "Alborz (البرز)",
					],
					[
						"code" => "EAZ",
						"name" => "East Azarbaijan (آذربایجان شرقی)",
					],
					[
						"code" => "WAZ",
						"name" => "West Azarbaijan (آذربایجان غربی)",
					],
					[
						"code" => "CHB",
						"name" => "Chaharmahal and Bakhtiari (چهارمحال و بختیاری)",
					],
					[
						"code" => "SKH",
						"name" => "South Khorasan (خراسان جنوبی)",
					],
					[
						"code" => "RKH",
						"name" => "Razavi Khorasan (خراسان رضوی)",
					],
					[
						"code" => "NKH",
						"name" => "North Khorasan (خراسان شمالی)",
					],
					[
						"code" => "SMN",
						"name" => "Semnan (سمنان)",
					],
					[
						"code" => "FRS",
						"name" => "Fars (فارس)",
					],
					[
						"code" => "QHM",
						"name" => "Qom (قم)",
					],
					[
						"code" => "KRD",
						"name" => "Kurdistan / کردستان)",
					],
					[
						"code" => "KBD",
						"name" => "Kohgiluyeh and BoyerAhmad (کهگیلوییه و بویراحمد)",
					],
					[
						"code" => "GLS",
						"name" => "Golestan (گلستان)",
					],
					[
						"code" => "GIL",
						"name" => "Gilan (گیلان)",
					],
					[
						"code" => "MZN",
						"name" => "Mazandaran (مازندران)",
					],
					[
						"code" => "MKZ",
						"name" => "Markazi (مرکزی)",
					],
					[
						"code" => "HRZ",
						"name" => "Hormozgan (هرمزگان)",
					],
					[
						"code" => "SBN",
						"name" => "Sistan and Baluchestan (سیستان و بلوچستان)",
					],
				],
			],
			// Italy Provinces
			[
				"country" => "IT",
				"territories_reference" => "Provinces",
				"territories" => [
					[
						"code" => "AG",
						"name" => "Agrigento",
					],
					[
						"code" => "AL",
						"name" => "Alessandria",
					],
					[
						"code" => "AN",
						"name" => "Ancona",
					],
					[
						"code" => "AO",
						"name" => "Aosta",
					],
					[
						"code" => "AR",
						"name" => "Arezzo",
					],
					[
						"code" => "AP",
						"name" => "Ascoli Piceno",
					],
					[
						"code" => "AT",
						"name" => "Asti",
					],
					[
						"code" => "AV",
						"name" => "Avellino",
					],
					[
						"code" => "BA",
						"name" => "Bari",
					],
					[
						"code" => "BT",
						"name" => "Barletta-Andria-Trani",
					],
					[
						"code" => "BL",
						"name" => "Belluno",
					],
					[
						"code" => "BN",
						"name" => "Benevento",
					],
					[
						"code" => "BG",
						"name" => "Bergamo",
					],
					[
						"code" => "BI",
						"name" => "Biella",
					],
					[
						"code" => "BO",
						"name" => "Bologna",
					],
					[
						"code" => "BZ",
						"name" => "Bolzano",
					],
					[
						"code" => "BS",
						"name" => "Brescia",
					],
					[
						"code" => "BR",
						"name" => "Brindisi",
					],
					[
						"code" => "CA",
						"name" => "Cagliari",
					],
					[
						"code" => "CL",
						"name" => "Caltanissetta",
					],
					[
						"code" => "CB",
						"name" => "Campobasso",
					],
					[
						"code" => "CE",
						"name" => "Caserta",
					],
					[
						"code" => "CT",
						"name" => "Catania",
					],
					[
						"code" => "CZ",
						"name" => "Catanzaro",
					],
					[
						"code" => "CH",
						"name" => "Chieti",
					],
					[
						"code" => "CO",
						"name" => "Como",
					],
					[
						"code" => "CS",
						"name" => "Cosenza",
					],
					[
						"code" => "CR",
						"name" => "Cremona",
					],
					[
						"code" => "KR",
						"name" => "Crotone",
					],
					[
						"code" => "CN",
						"name" => "Cuneo",
					],
					[
						"code" => "EN",
						"name" => "Enna",
					],
					[
						"code" => "FM",
						"name" => "Fermo",
					],
					[
						"code" => "FE",
						"name" => "Ferrara",
					],
					[
						"code" => "FI",
						"name" => "Firenze",
					],
					[
						"code" => "FG",
						"name" => "Foggia",
					],
					[
						"code" => "FC",
						"name" => "Forlì-Cesena",
					],
					[
						"code" => "FR",
						"name" => "Frosinone",
					],
					[
						"code" => "GE",
						"name" => "Genova",
					],
					[
						"code" => "GO",
						"name" => "Gorizia",
					],
					[
						"code" => "GR",
						"name" => "Grosseto",
					],
					[
						"code" => "IM",
						"name" => "Imperia",
					],
					[
						"code" => "IS",
						"name" => "Isernia",
					],
					[
						"code" => "SP",
						"name" => "La Spezia",
					],
					[
						"code" => "AQ",
						"name" => "L\'Aquila",
					],
					[
						"code" => "LT",
						"name" => "Latina",
					],
					[
						"code" => "LE",
						"name" => "Lecce",
					],
					[
						"code" => "LC",
						"name" => "Lecco",
					],
					[
						"code" => "LI",
						"name" => "Livorno",
					],
					[
						"code" => "LO",
						"name" => "Lodi",
					],
					[
						"code" => "LU",
						"name" => "Lucca",
					],
					[
						"code" => "MC",
						"name" => "Macerata",
					],
					[
						"code" => "MN",
						"name" => "Mantova",
					],
					[
						"code" => "MS",
						"name" => "Massa-Carrara",
					],
					[
						"code" => "MT",
						"name" => "Matera",
					],
					[
						"code" => "ME",
						"name" => "Messina",
					],
					[
						"code" => "MI",
						"name" => "Milano",
					],
					[
						"code" => "MO",
						"name" => "Modena",
					],
					[
						"code" => "MB",
						"name" => "Monza e della Brianza",
					],
					[
						"code" => "NA",
						"name" => "Napoli",
					],
					[
						"code" => "NO",
						"name" => "Novara",
					],
					[
						"code" => "NU",
						"name" => "Nuoro",
					],
					[
						"code" => "OR",
						"name" => "Oristano",
					],
					[
						"code" => "PD",
						"name" => "Padova",
					],
					[
						"code" => "PA",
						"name" => "Palermo",
					],
					[
						"code" => "PR",
						"name" => "Parma",
					],
					[
						"code" => "PV",
						"name" => "Pavia",
					],
					[
						"code" => "PG",
						"name" => "Perugia",
					],
					[
						"code" => "PU",
						"name" => "Pesaro e Urbino",
					],
					[
						"code" => "PE",
						"name" => "Pescara",
					],
					[
						"code" => "PC",
						"name" => "Piacenza",
					],
					[
						"code" => "PI",
						"name" => "Pisa",
					],
					[
						"code" => "PT",
						"name" => "Pistoia",
					],
					[
						"code" => "PN",
						"name" => "Pordenone",
					],
					[
						"code" => "PZ",
						"name" => "Potenza",
					],
					[
						"code" => "PO",
						"name" => "Prato",
					],
					[
						"code" => "RG",
						"name" => "Ragusa",
					],
					[
						"code" => "RA",
						"name" => "Ravenna",
					],
					[
						"code" => "RC",
						"name" => "Reggio Calabria",
					],
					[
						"code" => "RE",
						"name" => "Reggio Emilia",
					],
					[
						"code" => "RI",
						"name" => "Rieti",
					],
					[
						"code" => "RN",
						"name" => "Rimini",
					],
					[
						"code" => "RM",
						"name" => "Roma",
					],
					[
						"code" => "RO",
						"name" => "Rovigo",
					],
					[
						"code" => "SA",
						"name" => "Salerno",
					],
					[
						"code" => "SS",
						"name" => "Sassari",
					],
					[
						"code" => "SV",
						"name" => "Savona",
					],
					[
						"code" => "SI",
						"name" => "Siena",
					],
					[
						"code" => "SR",
						"name" => "Siracusa",
					],
					[
						"code" => "SO",
						"name" => "Sondrio",
					],
					[
						"code" => "SU",
						"name" => "Sud Sardegna",
					],
					[
						"code" => "TA",
						"name" => "Taranto",
					],
					[
						"code" => "TE",
						"name" => "Teramo",
					],
					[
						"code" => "TR",
						"name" => "Terni",
					],
					[
						"code" => "TO",
						"name" => "Torino",
					],
					[
						"code" => "TP",
						"name" => "Trapani",
					],
					[
						"code" => "TN",
						"name" => "Trento",
					],
					[
						"code" => "TV",
						"name" => "Treviso",
					],
					[
						"code" => "TS",
						"name" => "Trieste",
					],
					[
						"code" => "UD",
						"name" => "Udine",
					],
					[
						"code" => "VA",
						"name" => "Varese",
					],
					[
						"code" => "VE",
						"name" => "Venezia",
					],
					[
						"code" => "VB",
						"name" => "Verbano-Cusio-Ossola",
					],
					[
						"code" => "VC",
						"name" => "Vercelli",
					],
					[
						"code" => "VR",
						"name" => "Verona",
					],
					[
						"code" => "VV",
						"name" => "Vibo Valentia",
					],
					[
						"code" => "VI",
						"name" => "Vicenza",
					],
					[
						"code" => "VT",
						"name" => "Viterbo",
					],
				],
			],
			// Japan States
			[
				"country" => "JP",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "JP01",
						"name" => "Hokkaido",
					],
					[
						"code" => "JP02",
						"name" => "Aomori",
					],
					[
						"code" => "JP03",
						"name" => "Iwate",
					],
					[
						"code" => "JP04",
						"name" => "Miyagi",
					],
					[
						"code" => "JP05",
						"name" => "Akita",
					],
					[
						"code" => "JP06",
						"name" => "Yamagata",
					],
					[
						"code" => "JP07",
						"name" => "Fukushima",
					],
					[
						"code" => "JP08",
						"name" => "Ibaraki",
					],
					[
						"code" => "JP09",
						"name" => "Tochigi",
					],
					[
						"code" => "JP10",
						"name" => "Gunma",
					],
					[
						"code" => "JP11",
						"name" => "Saitama",
					],
					[
						"code" => "JP12",
						"name" => "Chiba",
					],
					[
						"code" => "JP13",
						"name" => "Tokyo",
					],
					[
						"code" => "JP14",
						"name" => "Kanagawa",
					],
					[
						"code" => "JP15",
						"name" => "Niigata",
					],
					[
						"code" => "JP16",
						"name" => "Toyama",
					],
					[
						"code" => "JP17",
						"name" => "Ishikawa",
					],
					[
						"code" => "JP18",
						"name" => "Fukui",
					],
					[
						"code" => "JP19",
						"name" => "Yamanashi",
					],
					[
						"code" => "JP20",
						"name" => "Nagano",
					],
					[
						"code" => "JP21",
						"name" => "Gifu",
					],
					[
						"code" => "JP22",
						"name" => "Shizuoka",
					],
					[
						"code" => "JP23",
						"name" => "Aichi",
					],
					[
						"code" => "JP24",
						"name" => "Mie",
					],
					[
						"code" => "JP25",
						"name" => "Shiga",
					],
					[
						"code" => "JP26",
						"name" => "Kyoto",
					],
					[
						"code" => "JP27",
						"name" => "Osaka",
					],
					[
						"code" => "JP28",
						"name" => "Hyogo",
					],
					[
						"code" => "JP29",
						"name" => "Nara",
					],
					[
						"code" => "JP30",
						"name" => "Wakayama",
					],
					[
						"code" => "JP31",
						"name" => "Tottori",
					],
					[
						"code" => "JP32",
						"name" => "Shimane",
					],
					[
						"code" => "JP33",
						"name" => "Okayama",
					],
					[
						"code" => "JP34",
						"name" => "Hiroshima",
					],
					[
						"code" => "JP35",
						"name" => "Yamaguchi",
					],
					[
						"code" => "JP36",
						"name" => "Tokushima",
					],
					[
						"code" => "JP37",
						"name" => "Kagawa",
					],
					[
						"code" => "JP38",
						"name" => "Ehime",
					],
					[
						"code" => "JP39",
						"name" => "Kochi",
					],
					[
						"code" => "JP40",
						"name" => "Fukuoka",
					],
					[
						"code" => "JP41",
						"name" => "Saga",
					],
					[
						"code" => "JP42",
						"name" => "Nagasaki",
					],
					[
						"code" => "JP43",
						"name" => "Kumamoto",
					],
					[
						"code" => "JP44",
						"name" => "Oita",
					],
					[
						"code" => "JP45",
						"name" => "Miyazaki",
					],
					[
						"code" => "JP46",
						"name" => "Kagoshima",
					],
					[
						"code" => "JP47",
						"name" => "Okinawa",
					],
				],
			],
			// Liberia Provinces
			[
				"country" => "LR",
				"territories_reference" => "Provinces",
				"territories" => [
					[
						"code" => "BM",
						"name" => "Bomi",
					],
					[
						"code" => "BN",
						"name" => "Bong",
					],
					[
						"code" => "GA",
						"name" => "Gbarpolu",
					],
					[
						"code" => "GB",
						"name" => "Grand Bassa",
					],
					[
						"code" => "GC",
						"name" => "Grand Cape Mount",
					],
					[
						"code" => "GG",
						"name" => "Grand Gedeh",
					],
					[
						"code" => "GK",
						"name" => "Grand Kru",
					],
					[
						"code" => "LO",
						"name" => "Lofa",
					],
					[
						"code" => "MA",
						"name" => "Margibi",
					],
					[
						"code" => "MY",
						"name" => "Maryland",
					],
					[
						"code" => "MO",
						"name" => "Montserrado",
					],
					[
						"code" => "NM",
						"name" => "Nimba",
					],
					[
						"code" => "RV",
						"name" => "Rivercess",
					],
					[
						"code" => "RG",
						"name" => "River Gee",
					],
					[
						"code" => "SN",
						"name" => "Sinoe",
					],
				],
			],
			//  Moldova States
			/*
											For more details check:
											https://ro.wikipedia.org/wiki/Organizarea_administrativ-teritorial%C4%83_a_Republicii_Moldova
											https://ro.wikipedia.org/wiki/Raioanele_Republicii_Moldova
											https://en.wikipedia.org/wiki/ISO_3166-2:MD
											https://en.wikipedia.org/wiki/Romanian_alphabet#Unicode_and_HTML
											*/
			[
				"country" => "MD",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "C",
						"name" => "Chișinău",
					],
					[
						"code" => "BL",
						"name" => "Bălți",
					],
					[
						"code" => "AN",
						"name" => "Anenii Noi",
					],
					[
						"code" => "BS",
						"name" => "Basarabeasca",
					],
					[
						"code" => "BR",
						"name" => "Briceni",
					],
					[
						"code" => "CH",
						"name" => "Cahul",
					],
					[
						"code" => "CT",
						"name" => "Cantemir",
					],
					[
						"code" => "CL",
						"name" => "Călărași",
					],
					[
						"code" => "CS",
						"name" => "Căușeni",
					],
					[
						"code" => "CM",
						"name" => "Cimișlia",
					],
					[
						"code" => "CR",
						"name" => "Criuleni",
					],
					[
						"code" => "DN",
						"name" => "Dondușeni",
					],
					[
						"code" => "DR",
						"name" => "Drochia",
					],
					[
						"code" => "DB",
						"name" => "Dubăsari",
					],
					[
						"code" => "ED",
						"name" => "Edineț",
					],
					[
						"code" => "FL",
						"name" => "Fălești",
					],
					[
						"code" => "FR",
						"name" => "Florești",
					],
					[
						"code" => "GE",
						"name" => "UTA Găgăuzia",
					],
					[
						"code" => "GL",
						"name" => "Glodeni",
					],
					[
						"code" => "HN",
						"name" => "Hîncești",
					],
					[
						"code" => "IL",
						"name" => "Ialoveni",
					],
					[
						"code" => "LV",
						"name" => "Leova",
					],
					[
						"code" => "NS",
						"name" => "Nisporeni",
					],
					[
						"code" => "OC",
						"name" => "Ocnița",
					],
					[
						"code" => "OR",
						"name" => "Orhei",
					],
					[
						"code" => "RZ",
						"name" => "Rezina",
					],
					[
						"code" => "RS",
						"name" => "Rîșcani",
					],
					[
						"code" => "SG",
						"name" => "Sîngerei",
					],
					[
						"code" => "SR",
						"name" => "Soroca",
					],
					[
						"code" => "ST",
						"name" => "Strășeni",
					],
					[
						"code" => "SD",
						"name" => "Șoldănești",
					],
					[
						"code" => "SV",
						"name" => "Ștefan Vodă",
					],
					[
						"code" => "TR",
						"name" => "Taraclia",
					],
					[
						"code" => "TL",
						"name" => "Telenești",
					],
					[
						"code" => "UN",
						"name" => "Ungheni",
					],
				],
			],
			// Mexico States
			[
				"country" => "MX",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "DF",
						"name" => "Ciudad de México",
					],
					[
						"code" => "JA",
						"name" => "Jalisco",
					],
					[
						"code" => "NL",
						"name" => "Nuevo León",
					],
					[
						"code" => "AG",
						"name" => "Aguascalientes",
					],
					[
						"code" => "BC",
						"name" => "Baja California",
					],
					[
						"code" => "BS",
						"name" => "Baja California Sur",
					],
					[
						"code" => "CM",
						"name" => "Campeche",
					],
					[
						"code" => "CS",
						"name" => "Chiapas",
					],
					[
						"code" => "CH",
						"name" => "Chihuahua",
					],
					[
						"code" => "CO",
						"name" => "Coahuila",
					],
					[
						"code" => "CL",
						"name" => "Colima",
					],
					[
						"code" => "DG",
						"name" => "Durango",
					],
					[
						"code" => "GT",
						"name" => "Guanajuato",
					],
					[
						"code" => "GR",
						"name" => "Guerrero",
					],
					[
						"code" => "HG",
						"name" => "Hidalgo",
					],
					// TODO: may need to change territory code to MX1 to avoid clash with the country code!?
					[
						"code" => "MX",
						"name" => "Estado de México",
					],
					[
						"code" => "MI",
						"name" => "Michoacán",
					],
					[
						"code" => "MO",
						"name" => "Morelos",
					],
					[
						"code" => "NA",
						"name" => "Nayarit",
					],
					[
						"code" => "OA",
						"name" => "Oaxaca",
					],
					[
						"code" => "PU",
						"name" => "Puebla",
					],
					[
						"code" => "QT",
						"name" => "Querétaro",
					],
					[
						"code" => "QR",
						"name" => "Quintana Roo",
					],
					[
						"code" => "SL",
						"name" => "San Luis Potosí",
					],
					[
						"code" => "SI",
						"name" => "Sinaloa",
					],
					[
						"code" => "SO",
						"name" => "Sonora",
					],
					[
						"code" => "TB",
						"name" => "Tabasco",
					],
					[
						"code" => "TM",
						"name" => "Tamaulipas",
					],
					[
						"code" => "TL",
						"name" => "Tlaxcala",
					],
					[
						"code" => "VE",
						"name" => "Veracruz",
					],
					[
						"code" => "YU",
						"name" => "Yucatán",
					],
					[
						"code" => "ZA",
						"name" => "Zacatecas",
					],
				],
			],
			// Malaysian States
			[
				"country" => "MY",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "JHR",
						"name" => "Johor",
					],
					[
						"code" => "KDH",
						"name" => "Kedah",
					],
					[
						"code" => "KTN",
						"name" => "Kelantan",
					],
					[
						"code" => "LBN",
						"name" => "Labuan",
					],
					[
						"code" => "MLK",
						"name" => "Malacca (Melaka)",
					],
					[
						"code" => "NSN",
						"name" => "Negeri Sembilan",
					],
					[
						"code" => "PHG",
						"name" => "Pahang",
					],
					[
						"code" => "PNG",
						"name" => "Penang (Pulau Pinang)",
					],
					[
						"code" => "PRK",
						"name" => "Perak",
					],
					[
						"code" => "PLS",
						"name" => "Perlis",
					],
					[
						"code" => "SBH",
						"name" => "Sabah",
					],
					[
						"code" => "SWK",
						"name" => "Sarawak",
					],
					[
						"code" => "SGR",
						"name" => "Selangor",
					],
					[
						"code" => "TRG",
						"name" => "Terengganu",
					],
					[
						"code" => "PJY",
						"name" => "Putrajaya",
					],
					[
						"code" => "KUL",
						"name" => "Kuala Lumpur",
					],
				],
			],
			// Nigerian Provinces
			[
				"country" => "NG",
				"territories_reference" => "Provinces",
				"territories" => [
					[
						"code" => "AB",
						"name" => "Abia",
					],
					[
						"code" => "FC",
						"name" => "Abuja",
					],
					[
						"code" => "AD",
						"name" => "Adamawa",
					],
					[
						"code" => "AK",
						"name" => "Akwa Ibom",
					],
					[
						"code" => "AN",
						"name" => "Anambra",
					],
					[
						"code" => "BA",
						"name" => "Bauchi",
					],
					[
						"code" => "BY",
						"name" => "Bayelsa",
					],
					[
						"code" => "BE",
						"name" => "Benue",
					],
					[
						"code" => "BO",
						"name" => "Borno",
					],
					[
						"code" => "CR",
						"name" => "Cross River",
					],
					[
						"code" => "DE",
						"name" => "Delta",
					],
					[
						"code" => "EB",
						"name" => "Ebonyi",
					],
					[
						"code" => "ED",
						"name" => "Edo",
					],
					[
						"code" => "EK",
						"name" => "Ekiti",
					],
					[
						"code" => "EN",
						"name" => "Enugu",
					],
					[
						"code" => "GO",
						"name" => "Gombe",
					],
					[
						"code" => "IM",
						"name" => "Imo",
					],
					[
						"code" => "JI",
						"name" => "Jigawa",
					],
					[
						"code" => "KD",
						"name" => "Kaduna",
					],
					[
						"code" => "KN",
						"name" => "Kano",
					],
					[
						"code" => "KT",
						"name" => "Katsina",
					],
					[
						"code" => "KE",
						"name" => "Kebbi",
					],
					[
						"code" => "KO",
						"name" => "Kogi",
					],
					[
						"code" => "KW",
						"name" => "Kwara",
					],
					[
						"code" => "LA",
						"name" => "Lagos",
					],
					[
						"code" => "NA",
						"name" => "Nasarawa",
					],
					[
						"code" => "NI",
						"name" => "Niger",
					],
					[
						"code" => "OG",
						"name" => "Ogun",
					],
					[
						"code" => "ON",
						"name" => "Ondo",
					],
					[
						"code" => "OS",
						"name" => "Osun",
					],
					[
						"code" => "OY",
						"name" => "Oyo",
					],
					[
						"code" => "PL",
						"name" => "Plateau",
					],
					[
						"code" => "RI",
						"name" => "Rivers",
					],
					[
						"code" => "SO",
						"name" => "Sokoto",
					],
					[
						"code" => "TA",
						"name" => "Taraba",
					],
					[
						"code" => "YO",
						"name" => "Yobe",
					],
					[
						"code" => "ZA",
						"name" => "Zamfara",
					],
				],
			],
			// Nepal States (Zones)
			[
				"country" => "NP",
				"territories_reference" => "Zones",
				"territories" => [
					[
						"code" => "BAG",
						"name" => "Bagmati",
					],
					[
						"code" => "BHE",
						"name" => "Bheri",
					],
					[
						"code" => "DHA",
						"name" => "Dhaulagiri",
					],
					[
						"code" => "GAN",
						"name" => "Gandaki",
					],
					[
						"code" => "JAN",
						"name" => "Janakpur",
					],
					[
						"code" => "KAR",
						"name" => "Karnali",
					],
					[
						"code" => "KOS",
						"name" => "Koshi",
					],
					[
						"code" => "LUM",
						"name" => "Lumbini",
					],
					[
						"code" => "MAH",
						"name" => "Mahakali",
					],
					[
						"code" => "MEC",
						"name" => "Mechi",
					],
					[
						"code" => "NAR",
						"name" => "Narayani",
					],
					[
						"code" => "RAP",
						"name" => "Rapti",
					],
					[
						"code" => "SAG",
						"name" => "Sagarmatha",
					],
					[
						"code" => "SET",
						"name" => "Seti",
					],
				],
			],
			// New Zealand States
			[
				"country" => "NZ",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "NL",
						"name" => "Northland",
					],
					[
						"code" => "AK",
						"name" => "Auckland",
					],
					[
						"code" => "WA",
						"name" => "Waikato",
					],
					[
						"code" => "BP",
						"name" => "Bay of Plenty",
					],
					[
						"code" => "TK",
						"name" => "Taranaki",
					],
					[
						"code" => "GI",
						"name" => "Gisborne",
					],
					[
						"code" => "HB",
						"name" => "Hawke’s Bay",
					],
					[
						"code" => "MW",
						"name" => "Manawatu-Wanganui",
					],
					[
						"code" => "WE",
						"name" => "Wellington",
					],
					[
						"code" => "NS",
						"name" => "Nelson",
					],
					[
						"code" => "MB",
						"name" => "Marlborough",
					],
					[
						"code" => "TM",
						"name" => "Tasman",
					],
					[
						"code" => "WC",
						"name" => "West Coast",
					],
					[
						"code" => "CT",
						"name" => "Canterbury",
					],
					[
						"code" => "OT",
						"name" => "Otago",
					],
					[
						"code" => "SL",
						"name" => "Southland",
					],
				],
			],
			// Peru States
			[
				"country" => "PE",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "CAL",
						"name" => "El Callao",
					],
					[
						"code" => "LMA",
						"name" => "Municipalidad Metropolitana de Lima",
					],
					[
						"code" => "AMA",
						"name" => "Amazonas",
					],
					[
						"code" => "ANC",
						"name" => "Ancash",
					],
					[
						"code" => "APU",
						"name" => "Apurímac",
					],
					[
						"code" => "ARE",
						"name" => "Arequipa",
					],
					[
						"code" => "AYA",
						"name" => "Ayacucho",
					],
					[
						"code" => "CAJ",
						"name" => "Cajamarca",
					],
					[
						"code" => "CUS",
						"name" => "Cusco",
					],
					[
						"code" => "HUV",
						"name" => "Huancavelica",
					],
					[
						"code" => "HUC",
						"name" => "Huánuco",
					],
					[
						"code" => "ICA",
						"name" => "Ica",
					],
					[
						"code" => "JUN",
						"name" => "Junín",
					],
					[
						"code" => "LAL",
						"name" => "La Libertad",
					],
					[
						"code" => "LAM",
						"name" => "Lambayeque",
					],
					[
						"code" => "LIM",
						"name" => "Lima",
					],
					[
						"code" => "LOR",
						"name" => "Loreto",
					],
					[
						"code" => "MDD",
						"name" => "Madre de Dios",
					],
					[
						"code" => "MOQ",
						"name" => "Moquegua",
					],
					[
						"code" => "PAS",
						"name" => "Pasco",
					],
					[
						"code" => "PIU",
						"name" => "Piura",
					],
					[
						"code" => "PUN",
						"name" => "Puno",
					],
					[
						"code" => "SAM",
						"name" => "San Martín",
					],
					[
						"code" => "TAC",
						"name" => "Tacna",
					],
					[
						"code" => "TUM",
						"name" => "Tumbes",
					],
					[
						"code" => "UCA",
						"name" => "Ucayali",
					],
				],
			],
			// Philippines Provinces
			[
				"country" => "PH",
				"territories_reference" => "Provinces",
				"territories" => [
					[
						"code" => "ABR",
						"name" => "Abra",
					],
					[
						"code" => "AGN",
						"name" => "Agusan del Norte",
					],
					[
						"code" => "AGS",
						"name" => "Agusan del Sur",
					],
					[
						"code" => "AKL",
						"name" => "Aklan",
					],
					[
						"code" => "ALB",
						"name" => "Albay",
					],
					[
						"code" => "ANT",
						"name" => "Antique",
					],
					[
						"code" => "APA",
						"name" => "Apayao",
					],
					[
						"code" => "AUR",
						"name" => "Aurora",
					],
					[
						"code" => "BAS",
						"name" => "Basilan",
					],
					[
						"code" => "BAN",
						"name" => "Bataan",
					],
					[
						"code" => "BTN",
						"name" => "Batanes",
					],
					[
						"code" => "BTG",
						"name" => "Batangas",
					],
					[
						"code" => "BEN",
						"name" => "Benguet",
					],
					[
						"code" => "BIL",
						"name" => "Biliran",
					],
					[
						"code" => "BOH",
						"name" => "Bohol",
					],
					[
						"code" => "BUK",
						"name" => "Bukidnon",
					],
					[
						"code" => "BUL",
						"name" => "Bulacan",
					],
					[
						"code" => "CAG",
						"name" => "Cagayan",
					],
					[
						"code" => "CAN",
						"name" => "Camarines Norte",
					],
					[
						"code" => "CAS",
						"name" => "Camarines Sur",
					],
					[
						"code" => "CAM",
						"name" => "Camiguin",
					],
					[
						"code" => "CAP",
						"name" => "Capiz",
					],
					[
						"code" => "CAT",
						"name" => "Catanduanes",
					],
					[
						"code" => "CAV",
						"name" => "Cavite",
					],
					[
						"code" => "CEB",
						"name" => "Cebu",
					],
					[
						"code" => "COM",
						"name" => "Compostela Valley",
					],
					[
						"code" => "NCO",
						"name" => "Cotabato",
					],
					[
						"code" => "DAV",
						"name" => "Davao del Norte",
					],
					[
						"code" => "DAS",
						"name" => "Davao del Sur",
					],
					// TODO: Needs to be updated when ISO code is assigned.
					[
						"code" => "DAC",
						"name" => "Davao Occidental",
					],
					[
						"code" => "DAO",
						"name" => "Davao Oriental",
					],
					[
						"code" => "DIN",
						"name" => "Dinagat Islands",
					],
					[
						"code" => "EAS",
						"name" => "Eastern Samar",
					],
					[
						"code" => "GUI",
						"name" => "Guimaras",
					],
					[
						"code" => "IFU",
						"name" => "Ifugao",
					],
					[
						"code" => "ILN",
						"name" => "Ilocos Norte",
					],
					[
						"code" => "ILS",
						"name" => "Ilocos Sur",
					],
					[
						"code" => "ILI",
						"name" => "Iloilo",
					],
					[
						"code" => "ISA",
						"name" => "Isabela",
					],
					[
						"code" => "KAL",
						"name" => "Kalinga",
					],
					[
						"code" => "LUN",
						"name" => "La Union",
					],
					[
						"code" => "LAG",
						"name" => "Laguna",
					],
					[
						"code" => "LAN",
						"name" => "Lanao del Norte",
					],
					[
						"code" => "LAS",
						"name" => "Lanao del Sur",
					],
					[
						"code" => "LEY",
						"name" => "Leyte",
					],
					[
						"code" => "MAG",
						"name" => "Maguindanao",
					],
					[
						"code" => "MAD",
						"name" => "Marinduque",
					],
					[
						"code" => "MAS",
						"name" => "Masbate",
					],
					[
						"code" => "MSC",
						"name" => "Misamis Occidental",
					],
					[
						"code" => "MSR",
						"name" => "Misamis Oriental",
					],
					[
						"code" => "MOU",
						"name" => "Mountain Province",
					],
					[
						"code" => "NEC",
						"name" => "Negros Occidental",
					],
					[
						"code" => "NER",
						"name" => "Negros Oriental",
					],
					[
						"code" => "NSA",
						"name" => "Northern Samar",
					],
					[
						"code" => "NUE",
						"name" => "Nueva Ecija",
					],
					[
						"code" => "NUV",
						"name" => "Nueva Vizcaya",
					],
					[
						"code" => "MDC",
						"name" => "Occidental Mindoro",
					],
					[
						"code" => "MDR",
						"name" => "Oriental Mindoro",
					],
					[
						"code" => "PLW",
						"name" => "Palawan",
					],
					[
						"code" => "PAM",
						"name" => "Pampanga",
					],
					[
						"code" => "PAN",
						"name" => "Pangasinan",
					],
					[
						"code" => "QUE",
						"name" => "Quezon",
					],
					[
						"code" => "QUI",
						"name" => "Quirino",
					],
					[
						"code" => "RIZ",
						"name" => "Rizal",
					],
					[
						"code" => "ROM",
						"name" => "Romblon",
					],
					[
						"code" => "WSA",
						"name" => "Samar",
					],
					[
						"code" => "SAR",
						"name" => "Sarangani",
					],
					[
						"code" => "SIQ",
						"name" => "Siquijor",
					],
					[
						"code" => "SOR",
						"name" => "Sorsogon",
					],
					[
						"code" => "SCO",
						"name" => "South Cotabato",
					],
					[
						"code" => "SLE",
						"name" => "Southern Leyte",
					],
					[
						"code" => "SUK",
						"name" => "Sultan Kudarat",
					],
					[
						"code" => "SLU",
						"name" => "Sulu",
					],
					[
						"code" => "SUN",
						"name" => "Surigao del Norte",
					],
					[
						"code" => "SUR",
						"name" => "Surigao del Sur",
					],
					[
						"code" => "TAR",
						"name" => "Tarlac",
					],
					[
						"code" => "TAW",
						"name" => "Tawi-Tawi",
					],
					[
						"code" => "ZMB",
						"name" => "Zambales",
					],
					[
						"code" => "ZAN",
						"name" => "Zamboanga del Norte",
					],
					[
						"code" => "ZAS",
						"name" => "Zamboanga del Sur",
					],
					[
						"code" => "ZSI",
						"name" => "Zamboanga Sibugay",
					],
					[
						"code" => "00",
						"name" => "Metro Manila",
					],
				],
			],
			// Pakistan's States
			[
				"country" => "PK",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "JK",
						"name" => "Azad Kashmir",
					],
					[
						"code" => "BA",
						"name" => "Balochistan",
					],
					[
						"code" => "TA",
						"name" => "FATA",
					],
					[
						"code" => "GB",
						"name" => "Gilgit Baltistan",
					],
					[
						"code" => "IS",
						"name" => "Islamabad Capital Territory",
					],
					[
						"code" => "KP",
						"name" => "Khyber Pakhtunkhwa",
					],
					[
						"code" => "PB",
						"name" => "Punjab",
					],
					[
						"code" => "SD",
						"name" => "Sindh",
					],
				],
			],
			// Paraguay States
			[
				"country" => "PY",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "PY-ASU",
						"name" => "Asunción",
					],
					[
						"code" => "PY-1",
						"name" => "Concepción",
					],
					[
						"code" => "PY-2",
						"name" => "San Pedro",
					],
					[
						"code" => "PY-3",
						"name" => "Cordillera",
					],
					[
						"code" => "PY-4",
						"name" => "Guairá",
					],
					[
						"code" => "PY-5",
						"name" => "Caaguazú",
					],
					[
						"code" => "PY-6",
						"name" => "Caazapá",
					],
					[
						"code" => "PY-7",
						"name" => "Itapúa",
					],
					[
						"code" => "PY-8",
						"name" => "Misiones",
					],
					[
						"code" => "PY-9",
						"name" => "Paraguarí",
					],
					[
						"code" => "PY-10",
						"name" => "Alto Paraná",
					],
					[
						"code" => "PY-11",
						"name" => "Central",
					],
					[
						"code" => "PY-12",
						"name" => "Ñeembucú",
					],
					[
						"code" => "PY-13",
						"name" => "Amambay",
					],
					[
						"code" => "PY-14",
						"name" => "Canindeyú",
					],
					[
						"code" => "PY-15",
						"name" => "Presidente Hayes",
					],
					[
						"code" => "PY-16",
						"name" => "Alto Paraguay",
					],
					[
						"code" => "PY-17",
						"name" => "Boquerón",
					],
				],
			],
			//  Romania States
			/*
													For more details check:
													https://ro.wikipedia.org/wiki/Jude%C8%9Bele_Rom%C3%A2niei
													*/
			[
				"country" => "RO",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "AB",
						"name" => "Alba",
					],
					[
						"code" => "AR",
						"name" => "Arad",
					],
					[
						"code" => "AG",
						"name" => "Argeș",
					],
					[
						"code" => "BC",
						"name" => "Bacău",
					],
					[
						"code" => "BH",
						"name" => "Bihor",
					],
					[
						"code" => "BN",
						"name" => "Bistrița-Năsăud",
					],
					[
						"code" => "BT",
						"name" => "Botoșani",
					],
					[
						"code" => "BR",
						"name" => "Brăila",
					],
					[
						"code" => "BV",
						"name" => "Brașov",
					],
					[
						"code" => "B",
						"name" => "București",
					],
					[
						"code" => "BZ",
						"name" => "Buzău",
					],
					[
						"code" => "CL",
						"name" => "Călărași",
					],
					[
						"code" => "CS",
						"name" => "Caraș-Severin",
					],
					[
						"code" => "CJ",
						"name" => "Cluj",
					],
					[
						"code" => "CT",
						"name" => "Constanța",
					],
					[
						"code" => "CV",
						"name" => "Covasna",
					],
					[
						"code" => "DB",
						"name" => "Dâmbovița",
					],
					[
						"code" => "DJ",
						"name" => "Dolj",
					],
					[
						"code" => "GL",
						"name" => "Galați",
					],
					[
						"code" => "GR",
						"name" => "Giurgiu",
					],
					[
						"code" => "GJ",
						"name" => "Gorj",
					],
					[
						"code" => "HR",
						"name" => "Harghita",
					],
					[
						"code" => "HD",
						"name" => "Hunedoara",
					],
					[
						"code" => "IL",
						"name" => "Ialomița",
					],
					[
						"code" => "IS",
						"name" => "Iași",
					],
					[
						"code" => "IF",
						"name" => "Ilfov",
					],
					[
						"code" => "MM",
						"name" => "Maramureș",
					],
					[
						"code" => "MH",
						"name" => "Mehedinți",
					],
					[
						"code" => "MS",
						"name" => "Mureș",
					],
					[
						"code" => "NT",
						"name" => "Neamț",
					],
					[
						"code" => "OT",
						"name" => "Olt",
					],
					[
						"code" => "PH",
						"name" => "Prahova",
					],
					[
						"code" => "SJ",
						"name" => "Sălaj",
					],
					[
						"code" => "SM",
						"name" => "Satu Mare",
					],
					[
						"code" => "SB",
						"name" => "Sibiu",
					],
					[
						"code" => "SV",
						"name" => "Suceava",
					],
					[
						"code" => "TR",
						"name" => "Teleorman",
					],
					[
						"code" => "TM",
						"name" => "Timiș",
					],
					[
						"code" => "TL",
						"name" => "Tulcea",
					],
					[
						"code" => "VL",
						"name" => "Vâlcea",
					],
					[
						"code" => "VS",
						"name" => "Vaslui",
					],
					[
						"code" => "VN",
						"name" => "Vrancea",
					],
				],
			],
			// Thailand States
			[
				"country" => "TH",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "TH-37",
						"name" => "Amnat Charoen",
					],
					[
						"code" => "TH-15",
						"name" => "Ang Thong",
					],
					[
						"code" => "TH-14",
						"name" => "Ayutthaya",
					],
					[
						"code" => "TH-10",
						"name" => "Bangkok",
					],
					[
						"code" => "TH-38",
						"name" => "Bueng Kan",
					],
					[
						"code" => "TH-31",
						"name" => "Buri Ram",
					],
					[
						"code" => "TH-24",
						"name" => "Chachoengsao",
					],
					[
						"code" => "TH-18",
						"name" => "Chai Nat",
					],
					[
						"code" => "TH-36",
						"name" => "Chaiyaphum",
					],
					[
						"code" => "TH-22",
						"name" => "Chanthaburi",
					],
					[
						"code" => "TH-50",
						"name" => "Chiang Mai",
					],
					[
						"code" => "TH-57",
						"name" => "Chiang Rai",
					],
					[
						"code" => "TH-20",
						"name" => "Chonburi",
					],
					[
						"code" => "TH-86",
						"name" => "Chumphon",
					],
					[
						"code" => "TH-46",
						"name" => "Kalasin",
					],
					[
						"code" => "TH-62",
						"name" => "Kamphaeng Phet",
					],
					[
						"code" => "TH-71",
						"name" => "Kanchanaburi",
					],
					[
						"code" => "TH-40",
						"name" => "Khon Kaen",
					],
					[
						"code" => "TH-81",
						"name" => "Krabi",
					],
					[
						"code" => "TH-52",
						"name" => "Lampang",
					],
					[
						"code" => "TH-51",
						"name" => "Lamphun",
					],
					[
						"code" => "TH-42",
						"name" => "Loei",
					],
					[
						"code" => "TH-16",
						"name" => "Lopburi",
					],
					[
						"code" => "TH-58",
						"name" => "Mae Hong Son",
					],
					[
						"code" => "TH-44",
						"name" => "Maha Sarakham",
					],
					[
						"code" => "TH-49",
						"name" => "Mukdahan",
					],
					[
						"code" => "TH-26",
						"name" => "Nakhon Nayok",
					],
					[
						"code" => "TH-73",
						"name" => "Nakhon Pathom",
					],
					[
						"code" => "TH-48",
						"name" => "Nakhon Phanom",
					],
					[
						"code" => "TH-30",
						"name" => "Nakhon Ratchasima",
					],
					[
						"code" => "TH-60",
						"name" => "Nakhon Sawan",
					],
					[
						"code" => "TH-80",
						"name" => "Nakhon Si Thammarat",
					],
					[
						"code" => "TH-55",
						"name" => "Nan",
					],
					[
						"code" => "TH-96",
						"name" => "Narathiwat",
					],
					[
						"code" => "TH-39",
						"name" => "Nong Bua Lam Phu",
					],
					[
						"code" => "TH-43",
						"name" => "Nong Khai",
					],
					[
						"code" => "TH-12",
						"name" => "Nonthaburi",
					],
					[
						"code" => "TH-13",
						"name" => "Pathum Thani",
					],
					[
						"code" => "TH-94",
						"name" => "Pattani",
					],
					[
						"code" => "TH-82",
						"name" => "Phang Nga",
					],
					[
						"code" => "TH-93",
						"name" => "Phatthalung",
					],
					[
						"code" => "TH-56",
						"name" => "Phayao",
					],
					[
						"code" => "TH-67",
						"name" => "Phetchabun",
					],
					[
						"code" => "TH-76",
						"name" => "Phetchaburi",
					],
					[
						"code" => "TH-66",
						"name" => "Phichit",
					],
					[
						"code" => "TH-65",
						"name" => "Phitsanulok",
					],
					[
						"code" => "TH-54",
						"name" => "Phrae",
					],
					[
						"code" => "TH-83",
						"name" => "Phuket",
					],
					[
						"code" => "TH-25",
						"name" => "Prachin Buri",
					],
					[
						"code" => "TH-77",
						"name" => "Prachuap Khiri Khan",
					],
					[
						"code" => "TH-85",
						"name" => "Ranong",
					],
					[
						"code" => "TH-70",
						"name" => "Ratchaburi",
					],
					[
						"code" => "TH-21",
						"name" => "Rayong",
					],
					[
						"code" => "TH-45",
						"name" => "Roi Et",
					],
					[
						"code" => "TH-27",
						"name" => "Sa Kaeo",
					],
					[
						"code" => "TH-47",
						"name" => "Sakon Nakhon",
					],
					[
						"code" => "TH-11",
						"name" => "Samut Prakan",
					],
					[
						"code" => "TH-74",
						"name" => "Samut Sakhon",
					],
					[
						"code" => "TH-75",
						"name" => "Samut Songkhram",
					],
					[
						"code" => "TH-19",
						"name" => "Saraburi",
					],
					[
						"code" => "TH-91",
						"name" => "Satun",
					],
					[
						"code" => "TH-17",
						"name" => "Sing Buri",
					],
					[
						"code" => "TH-33",
						"name" => "Sisaket",
					],
					[
						"code" => "TH-90",
						"name" => "Songkhla",
					],
					[
						"code" => "TH-64",
						"name" => "Sukhothai",
					],
					[
						"code" => "TH-72",
						"name" => "Suphan Buri",
					],
					[
						"code" => "TH-84",
						"name" => "Surat Thani",
					],
					[
						"code" => "TH-32",
						"name" => "Surin",
					],
					[
						"code" => "TH-63",
						"name" => "Tak",
					],
					[
						"code" => "TH-92",
						"name" => "Trang",
					],
					[
						"code" => "TH-23",
						"name" => "Trat",
					],
					[
						"code" => "TH-34",
						"name" => "Ubon Ratchathani",
					],
					[
						"code" => "TH-41",
						"name" => "Udon Thani",
					],
					[
						"code" => "TH-61",
						"name" => "Uthai Thani",
					],
					[
						"code" => "TH-53",
						"name" => "Uttaradit",
					],
					[
						"code" => "TH-95",
						"name" => "Yala",
					],
					[
						"code" => "TH-35",
						"name" => "Yasothon",
					],
				],
			],
			// Turkey States
			[
				"country" => "TR",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "TR01",
						"name" => "Adana",
					],
					[
						"code" => "TR02",
						"name" => "Adıyaman",
					],
					[
						"code" => "TR03",
						"name" => "Afyon",
					],
					[
						"code" => "TR04",
						"name" => "Ağrı",
					],
					[
						"code" => "TR05",
						"name" => "Amasya",
					],
					[
						"code" => "TR06",
						"name" => "Ankara",
					],
					[
						"code" => "TR07",
						"name" => "Antalya",
					],
					[
						"code" => "TR08",
						"name" => "Artvin",
					],
					[
						"code" => "TR09",
						"name" => "Aydın",
					],
					[
						"code" => "TR10",
						"name" => "Balıkesir",
					],
					[
						"code" => "TR11",
						"name" => "Bilecik",
					],
					[
						"code" => "TR12",
						"name" => "Bingöl",
					],
					[
						"code" => "TR13",
						"name" => "Bitlis",
					],
					[
						"code" => "TR14",
						"name" => "Bolu",
					],
					[
						"code" => "TR15",
						"name" => "Burdur",
					],
					[
						"code" => "TR16",
						"name" => "Bursa",
					],
					[
						"code" => "TR17",
						"name" => "Çanakkale",
					],
					[
						"code" => "TR18",
						"name" => "Çankırı",
					],
					[
						"code" => "TR19",
						"name" => "Çorum",
					],
					[
						"code" => "TR20",
						"name" => "Denizli",
					],
					[
						"code" => "TR21",
						"name" => "Diyarbakır",
					],
					[
						"code" => "TR22",
						"name" => "Edirne",
					],
					[
						"code" => "TR23",
						"name" => "Elazığ",
					],
					[
						"code" => "TR24",
						"name" => "Erzincan",
					],
					[
						"code" => "TR25",
						"name" => "Erzurum",
					],
					[
						"code" => "TR26",
						"name" => "Eskişehir",
					],
					[
						"code" => "TR27",
						"name" => "Gaziantep",
					],
					[
						"code" => "TR28",
						"name" => "Giresun",
					],
					[
						"code" => "TR29",
						"name" => "Gümüşhane",
					],
					[
						"code" => "TR30",
						"name" => "Hakkari",
					],
					[
						"code" => "TR31",
						"name" => "Hatay",
					],
					[
						"code" => "TR32",
						"name" => "Isparta",
					],
					[
						"code" => "TR33",
						"name" => "İçel",
					],
					[
						"code" => "TR34",
						"name" => "İstanbul",
					],
					[
						"code" => "TR35",
						"name" => "İzmir",
					],
					[
						"code" => "TR36",
						"name" => "Kars",
					],
					[
						"code" => "TR37",
						"name" => "Kastamonu",
					],
					[
						"code" => "TR38",
						"name" => "Kayseri",
					],
					[
						"code" => "TR39",
						"name" => "Kırklareli",
					],
					[
						"code" => "TR40",
						"name" => "Kırşehir",
					],
					[
						"code" => "TR41",
						"name" => "Kocaeli",
					],
					[
						"code" => "TR42",
						"name" => "Konya",
					],
					[
						"code" => "TR43",
						"name" => "Kütahya",
					],
					[
						"code" => "TR44",
						"name" => "Malatya",
					],
					[
						"code" => "TR45",
						"name" => "Manisa",
					],
					[
						"code" => "TR46",
						"name" => "Kahramanmaraş",
					],
					[
						"code" => "TR47",
						"name" => "Mardin",
					],
					[
						"code" => "TR48",
						"name" => "Muğla",
					],
					[
						"code" => "TR49",
						"name" => "Muş",
					],
					[
						"code" => "TR50",
						"name" => "Nevşehir",
					],
					[
						"code" => "TR51",
						"name" => "Niğde",
					],
					[
						"code" => "TR52",
						"name" => "Ordu",
					],
					[
						"code" => "TR53",
						"name" => "Rize",
					],
					[
						"code" => "TR54",
						"name" => "Sakarya",
					],
					[
						"code" => "TR55",
						"name" => "Samsun",
					],
					[
						"code" => "TR56",
						"name" => "Siirt",
					],
					[
						"code" => "TR57",
						"name" => "Sinop",
					],
					[
						"code" => "TR58",
						"name" => "Sivas",
					],
					[
						"code" => "TR59",
						"name" => "Tekirdağ",
					],
					[
						"code" => "TR60",
						"name" => "Tokat",
					],
					[
						"code" => "TR61",
						"name" => "Trabzon",
					],
					[
						"code" => "TR62",
						"name" => "Tunceli",
					],
					[
						"code" => "TR63",
						"name" => "Şanlıurfa",
					],
					[
						"code" => "TR64",
						"name" => "Uşak",
					],
					[
						"code" => "TR65",
						"name" => "Van",
					],
					[
						"code" => "TR66",
						"name" => "Yozgat",
					],
					[
						"code" => "TR67",
						"name" => "Zonguldak",
					],
					[
						"code" => "TR68",
						"name" => "Aksaray",
					],
					[
						"code" => "TR69",
						"name" => "Bayburt",
					],
					[
						"code" => "TR70",
						"name" => "Karaman",
					],
					[
						"code" => "TR71",
						"name" => "Kırıkkale",
					],
					[
						"code" => "TR72",
						"name" => "Batman",
					],
					[
						"code" => "TR73",
						"name" => "Şırnak",
					],
					[
						"code" => "TR74",
						"name" => "Bartın",
					],
					[
						"code" => "TR75",
						"name" => "Ardahan",
					],
					[
						"code" => "TR76",
						"name" => "Iğdır",
					],
					[
						"code" => "TR77",
						"name" => "Yalova",
					],
					[
						"code" => "TR78",
						"name" => "Karabük",
					],
					[
						"code" => "TR79",
						"name" => "Kilis",
					],
					[
						"code" => "TR80",
						"name" => "Osmaniye",
					],
					[
						"code" => "TR81",
						"name" => "Düzce",
					],
				],
			],
			// Tanzania States
			[
				"country" => "TZ",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "TZ01",
						"name" => "Arusha",
					],
					[
						"code" => "TZ02",
						"name" => "Dar es Salaam",
					],
					[
						"code" => "TZ03",
						"name" => "Dodoma",
					],
					[
						"code" => "TZ04",
						"name" => "Iringa",
					],
					[
						"code" => "TZ05",
						"name" => "Kagera",
					],
					[
						"code" => "TZ06",
						"name" => "Pemba North",
					],
					[
						"code" => "TZ07",
						"name" => "Zanzibar North",
					],
					[
						"code" => "TZ08",
						"name" => "Kigoma",
					],
					[
						"code" => "TZ09",
						"name" => "Kilimanjaro",
					],
					[
						"code" => "TZ10",
						"name" => "Pemba South",
					],
					[
						"code" => "TZ11",
						"name" => "Zanzibar South",
					],
					[
						"code" => "TZ12",
						"name" => "Lindi",
					],
					[
						"code" => "TZ13",
						"name" => "Mara",
					],
					[
						"code" => "TZ14",
						"name" => "Mbeya",
					],
					[
						"code" => "TZ15",
						"name" => "Zanzibar West",
					],
					[
						"code" => "TZ16",
						"name" => "Morogoro",
					],
					[
						"code" => "TZ17",
						"name" => "Mtwara",
					],
					[
						"code" => "TZ18",
						"name" => "Mwanza",
					],
					[
						"code" => "TZ19",
						"name" => "Coast",
					],
					[
						"code" => "TZ20",
						"name" => "Rukwa",
					],
					[
						"code" => "TZ21",
						"name" => "Ruvuma",
					],
					[
						"code" => "TZ22",
						"name" => "Shinyanga",
					],
					[
						"code" => "TZ23",
						"name" => "Singida",
					],
					[
						"code" => "TZ24",
						"name" => "Tabora",
					],
					[
						"code" => "TZ25",
						"name" => "Tanga",
					],
					[
						"code" => "TZ26",
						"name" => "Manyara",
					],
					[
						"code" => "TZ27",
						"name" => "Geita",
					],
					[
						"code" => "TZ28",
						"name" => "Katavi",
					],
					[
						"code" => "TZ29",
						"name" => "Njombe",
					],
					[
						"code" => "TZ30",
						"name" => "Simiyu",
					],
				],
			],
			// United States
			[
				"country" => "US",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "AL",
						"name" => "Alabama",
					],
					[
						"code" => "AK",
						"name" => "Alaska",
					],
					[
						"code" => "AZ",
						"name" => "Arizona",
					],
					[
						"code" => "AR",
						"name" => "Arkansas",
					],
					[
						"code" => "CA",
						"name" => "California",
					],
					[
						"code" => "CO",
						"name" => "Colorado",
					],
					[
						"code" => "CT",
						"name" => "Connecticut",
					],
					[
						"code" => "DE",
						"name" => "Delaware",
					],
					[
						"code" => "DC",
						"name" => "District Of Columbia",
					],
					[
						"code" => "FL",
						"name" => "Florida",
					],
					[
						"code" => "GA",
						"name" => "Georgia",
					],
					[
						"code" => "HI",
						"name" => "Hawaii",
					],
					[
						"code" => "ID",
						"name" => "Idaho",
					],
					[
						"code" => "IL",
						"name" => "Illinois",
					],
					[
						"code" => "IN",
						"name" => "Indiana",
					],
					[
						"code" => "IA",
						"name" => "Iowa",
					],
					[
						"code" => "KS",
						"name" => "Kansas",
					],
					[
						"code" => "KY",
						"name" => "Kentucky",
					],
					[
						"code" => "LA",
						"name" => "Louisiana",
					],
					[
						"code" => "ME",
						"name" => "Maine",
					],
					[
						"code" => "MD",
						"name" => "Maryland",
					],
					[
						"code" => "MA",
						"name" => "Massachusetts",
					],
					[
						"code" => "MI",
						"name" => "Michigan",
					],
					[
						"code" => "MN",
						"name" => "Minnesota",
					],
					[
						"code" => "MS",
						"name" => "Mississippi",
					],
					[
						"code" => "MO",
						"name" => "Missouri",
					],
					[
						"code" => "MT",
						"name" => "Montana",
					],
					[
						"code" => "NE",
						"name" => "Nebraska",
					],
					[
						"code" => "NV",
						"name" => "Nevada",
					],
					[
						"code" => "NH",
						"name" => "New Hampshire",
					],
					[
						"code" => "NJ",
						"name" => "New Jersey",
					],
					[
						"code" => "NM",
						"name" => "New Mexico",
					],
					[
						"code" => "NY",
						"name" => "New York",
					],
					[
						"code" => "NC",
						"name" => "North Carolina",
					],
					[
						"code" => "ND",
						"name" => "North Dakota",
					],
					[
						"code" => "OH",
						"name" => "Ohio",
					],
					[
						"code" => "OK",
						"name" => "Oklahoma",
					],
					[
						"code" => "OR",
						"name" => "Oregon",
					],
					[
						"code" => "PA",
						"name" => "Pennsylvania",
					],
					[
						"code" => "RI",
						"name" => "Rhode Island",
					],
					[
						"code" => "SC",
						"name" => "South Carolina",
					],
					[
						"code" => "SD",
						"name" => "South Dakota",
					],
					[
						"code" => "TN",
						"name" => "Tennessee",
					],
					[
						"code" => "TX",
						"name" => "Texas",
					],
					[
						"code" => "UT",
						"name" => "Utah",
					],
					[
						"code" => "VT",
						"name" => "Vermont",
					],
					[
						"code" => "VA",
						"name" => "Virginia",
					],
					[
						"code" => "WA",
						"name" => "Washington",
					],
					[
						"code" => "WV",
						"name" => "West Virginia",
					],
					[
						"code" => "WI",
						"name" => "Wisconsin",
					],
					[
						"code" => "WY",
						"name" => "Wyoming",
					],
					[
						"code" => "AA",
						"name" => "Armed Forces (AA)",
					],
					[
						"code" => "AE",
						"name" => "Armed Forces (AE)",
					],
					[
						"code" => "AP",
						"name" => "Armed Forces (AP)",
					],
				],
			],
			// South African States
			[
				"country" => "ZA",
				"territories_reference" => "States",
				"territories" => [
					[
						"code" => "EC",
						"name" => "Eastern Cape",
					],
					[
						"code" => "FS",
						"name" => "Free State",
					],
					[
						"code" => "GP",
						"name" => "Gauteng",
					],
					[
						"code" => "KZN",
						"name" => "KwaZulu-Natal",
					],
					[
						"code" => "LP",
						"name" => "Limpopo",
					],
					[
						"code" => "MP",
						"name" => "Mpumalanga",
					],
					[
						"code" => "NC",
						"name" => "Northern Cape",
					],
					[
						"code" => "NW",
						"name" => "North West",
					],
					[
						"code" => "WC",
						"name" => "Western Cape",
					],
				],
			],
		];

		return $territories;
	}

	/**
	 * Get Country Territory By Code.
	 *
	 * @param mixed $countryCode
	 * @return mixed
	 */
	public function getCountryTerritoryByCode($countryCode) {
		$foundCountryTerritory = null;
		if (empty($countryCode)) {
			return $foundCountryTerritory;
		}
		//----------
		$territories = $this->getTerritories();
		foreach ($territories as $territory) {
			if (\strtolower($territory['country']) === \strtolower($countryCode)) {
				$foundCountryTerritory = $territory;
				break;
			}
		}
		return $foundCountryTerritory;
	}
}
