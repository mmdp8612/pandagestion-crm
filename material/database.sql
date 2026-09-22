CREATE TABLE IF NOT EXISTS `tip_antiguedad` (
  `IdAntiguedad` varchar(3) NOT NULL DEFAULT '' COMMENT 'id Antiguedad',
  `Descrip` char(15) DEFAULT NULL COMMENT 'Descripcion',
  `Orden` int(2) DEFAULT NULL COMMENT 'Orden de Aparicion',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdAntiguedad`)
);

INSERT INTO `tip_antiguedad` (`IdAntiguedad`, `Descrip`, `Orden`, `Hab`) VALUES
	('A10', 'Menor a 10', 30, 1),
	('A15', 'Menor a 15', 40, 1),
	('A20', 'Menor a 20', 50, 1),
	('A25', 'Menor a 25', 55, 1),
	('A30', 'Menor a 30', 56, 1),
	('A35', 'Menor a 35', 57, 1),
	('A40', 'Menor a 40', 58, 1),
	('A45', 'Menos a 45', 59, 1),
	('A50', 'Menor a 50', 60, 1),
	('A99', 'Mas de 50', 80, 1),
	('AES', 'A Estrenar', 10, 1),
	('AN5', 'Menor a 5', 20, 1),
	('ENC', 'En Construcción', 5, 1),
	('SIN', 'Sin Determinar', 0, 1);

CREATE TABLE IF NOT EXISTS `tip_cochera` (
  `IdCochera` char(3) NOT NULL DEFAULT '' COMMENT 'idCochera',
  `Descrip` char(20) DEFAULT NULL COMMENT 'Descripcion',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdCochera`)
);

INSERT INTO `tip_cochera` (`IdCochera`, `Descrip`, `Hab`) VALUES
	('CCU', 'Cochera Cubierta', -1),
	('COC', 'Cochera', -1),
	('DES', 'Cochera Descubierta', -1),
	('ENT', 'Entrada de Auto', -1),
	('EPA', 'Entrada Pasante', -1),
	('GAR', 'Garage', -1),
	('GPA', 'Garage Pasante', -1),
	('OPT', 'Optativa', -1),
	('PAS', 'Pasante', -1),
	('PLA', 'Playa Estacionam', -1),
	('SCO', 'Sin Cochera', -1),
	('SEM', 'Semicubierta', -1),
	('SUM', 'Sumergida', -1);

CREATE TABLE IF NOT EXISTS `tip_comercializacion` (
  `IdComercializacion` char(3) NOT NULL DEFAULT '' COMMENT 'idComercializacion',
  `Descrip` char(15) NOT NULL DEFAULT '' COMMENT 'Descripcion',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdComercializacion`)
);

INSERT INTO `tip_comercializacion` (`IdComercializacion`, `Descrip`, `Hab`) VALUES
	('A-V', 'Ambos V/A', -1),
	('ALQ', 'Alquiler', -1),
	('ATE', 'Alq Temporario', -1),
	('FON', 'Fondo de Comerc', 0),
	('VTA', 'Venta', -1);

CREATE TABLE IF NOT EXISTS `tip_localidad` (
  `IdLocalidad` smallint(6) NOT NULL DEFAULT '0' COMMENT 'idLocalidad',
  `Descrip` char(60) DEFAULT NULL COMMENT 'Descripcion',
  `idPartido` char(4) DEFAULT NULL COMMENT 'idPartido',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdLocalidad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Localidades';

CREATE TABLE IF NOT EXISTS `tip_orientacion` (
  `IdOrientacion` char(2) NOT NULL DEFAULT '' COMMENT 'idOrientacion',
  `Descrip` char(15) NOT NULL DEFAULT '' COMMENT 'Descripcion',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdOrientacion`)
)

INSERT INTO `tip_orientacion` (`IdOrientacion`, `Descrip`, `Hab`) VALUES
	('E', 'Este', -1),
	('N', 'Norte', -1),
	('NE', 'Noreste', -1),
	('NO', 'Noroeste', -1),
	('O', 'Oeste', -1),
	('S', 'Sur', -1),
	('SE', 'Sudeste', -1),
	('SN', 'Indefinido', -1),
	('SO', 'Sudoeste', -1);

CREATE TABLE IF NOT EXISTS `tip_provincia` (
  `IdProvincia` char(3) NOT NULL DEFAULT '' COMMENT 'idProvincia',
  `Descrip` char(20) NOT NULL DEFAULT '' COMMENT 'Descripcion',
  `idPais` char(3) DEFAULT NULL COMMENT 'idPais',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdProvincia`)
);

INSERT INTO `tip_provincia` (`IdProvincia`, `Descrip`, `idPais`, `Hab`) VALUES
	('BUE', 'Buenos Aires', 'ARG', -1),
	('CAT', 'Catamarca', 'ARG', -1),
	('CBA', 'Cordoba', 'ARG', -1),
	('CFE', 'Capital Federal', 'ARG', -1),
	('CHA', 'Chaco', 'ARG', -1),
	('CHU', 'Chubut', 'ARG', -1),
	('COR', 'Corrientes', 'ARG', -1),
	('ENT', 'Entre Rios', 'ARG', -1),
	('FOR', 'Formosa', 'ARG', -1),
	('JUJ', 'Jujuy', 'ARG', -1),
	('LAP', 'La Pampa', 'ARG', -1),
	('LAR', 'La Rioja', 'ARG', -1),
	('MAL', 'Maldonado', 'URU', -1),
	('MEN', 'Mendoza', 'ARG', -1),
	('MIS', 'Misiones', 'ARG', -1),
	('MON', 'Montevideo', 'URY', -1),
	('NEU', 'Neuquen', 'ARG', -1),
	('RIO', 'Rio Negro', 'ARG', -1),
	('SAL', 'Salta', 'ARG', -1),
	('SCA', 'Santa Catarina', 'BRA', -1),
	('SCR', 'Santa Cruz', 'ARG', -1),
	('SGO', 'Santiago del Estero', 'ARG', -1),
	('SJU', 'San Juan', 'ARG', -1),
	('SLU', 'San Luis', 'ARG', -1),
	('STA', 'Santa Fe', 'ARG', -1),
	('TIE', 'Tierra del Fuego', 'ARG', -1),
	('TUC', 'Tucuman', 'ARG', -1);

CREATE TABLE IF NOT EXISTS `tip_tipologia` (
  `IdTipologia` char(4) NOT NULL DEFAULT '' COMMENT 'idTipologia',
  `Descrip` char(20) NOT NULL DEFAULT '' COMMENT 'Descripcion',
  `TipoGral` char(15) DEFAULT NULL COMMENT 'Tipologia General',
  `OrdTipoGral` smallint(6) DEFAULT '0' COMMENT 'Orden Tipologia General',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdTipologia`)
);

INSERT INTO `tip_tipologia` (`IdTipologia`, `Descrip`, `TipoGral`, `OrdTipoGral`, `Hab`) VALUES
	('BAUL', 'Baulera', 'Cocheras', 60, -1),
	('CAMP', 'Campo', 'Campos', 80, -1),
	('CAPH', 'PH', 'Casas', 10, -1),
	('CASA', 'Casa', 'Casas', 10, -1),
	('CHAC', 'Chacra', 'Campos', 80, -1),
	('CHAL', 'Chalet', 'Casas', 10, -1),
	('COCH', 'Cochera', 'Cocheras', 60, -1),
	('CQUI', 'Casa Quinta', 'Casas', 10, -1),
	('CTRY', 'Country', 'Casas', 10, -1),
	('DEPO', 'Depósito', 'Industriales', 70, -1),
	('DPTO', 'Departamento', 'Departamentos', 20, -1),
	('DUPL', 'Duplex', 'Casas', 10, -1),
	('FDOC', 'Fondo de Comercio', 'Comerciales', 40, -1),
	('FRAC', 'Fracción', 'Lotes', 30, -1),
	('GALP', 'Galpón', 'Industriales', 70, -1),
	('INDU', 'Industria', 'Industriales', 70, -1),
	('LOCA', 'Local', 'Comerciales', 40, -1),
	('LOFT', 'Loft', 'Departamentos', 20, -1),
	('LOSH', 'Local Shopping', 'Comerciales', 40, -1),
	('LOTE', 'Lote', 'Lotes', 30, -1),
	('NEGE', 'Negocios Especiales', 'Negocios Especi', 90, -1),
	('OFIC', 'Oficina', 'Oficinas', 50, -1),
	('PFAB', 'Planta Fabril', 'Industriales', 70, -1),
	('PISO', 'Piso', 'Departamentos', 20, -1),
	('SEMI', 'Semipiso', 'Departamentos', 20, -1),
	('TALL', 'Taller', 'Industriales', 70, -1),
	('TRIP', 'Triplex', 'Casas', 10, -1);

CREATE TABLE IF NOT EXISTS `tip_tipomoneda` (
  `idTipoMoneda` smallint(6) NOT NULL DEFAULT '0' COMMENT 'idTipo de Moneda',
  `Descrip` char(10) NOT NULL DEFAULT '' COMMENT 'Descripcion',
  `Simbolo` char(3) DEFAULT NULL COMMENT 'Simbolo',
  `Hab` tinyint(1) DEFAULT NULL COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`idTipoMoneda`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Tipo de moneda';

INSERT INTO `tip_tipomoneda` (`idTipoMoneda`, `Descrip`, `Simbolo`, `Hab`) VALUES
	(0, 'Dolares', 'u$s', -1),
	(1, 'Pesos', '$', -1);

CREATE TABLE IF NOT EXISTS `tip_uso` (
  `IdUso` char(4) NOT NULL DEFAULT '' COMMENT 'idUso',
  `Descrip` char(15) NOT NULL DEFAULT '' COMMENT 'Descripcion',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdUso`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Usos de la propiedad';

INSERT INTO `tip_uso` (`IdUso`, `Descrip`, `Hab`) VALUES
	('APRO', 'Apta Profes', -1),
	('COME', 'Comercial', -1),
	('INDU', 'Industrial', -1),
	('TODO', 'Todo Destino', -1),
	('VIVI', 'Vivienda', -1);

CREATE TABLE IF NOT EXISTS `tip_vista` (
  `IdVista` char(7) NOT NULL DEFAULT '' COMMENT 'idVista',
  `Descrip` char(15) NOT NULL DEFAULT '' COMMENT 'Descripcion',
  `Hab` tinyint(1) DEFAULT '0' COMMENT '1=Hab 0=Deshabilitado',
  PRIMARY KEY (`IdVista`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='Vista de la propiedad';

INSERT INTO `tip_vista` (`IdVista`, `Descrip`, `Hab`) VALUES
	('C/FTE', 'Contrafrente', -1),
	('FRENTE', 'Al Frente', -1),
	('INTERNO', 'Interno', -1),
	('LATERAL', 'Lateral', -1);
