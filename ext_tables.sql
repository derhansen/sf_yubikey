#
# Table structure for table 'be_users'
#
CREATE TABLE be_users (
	tx_sfyubikey_yubikey_enable tinyint(3) DEFAULT '0' NOT NULL,
	tx_sfyubikey_yubikey_id tinytext
);

#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_sfyubikey_yubikey_enable tinyint(3) DEFAULT '0' NOT NULL,
	tx_sfyubikey_yubikey_id tinytext
);