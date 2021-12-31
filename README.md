# Magento 2 DB Split (Master - Slaves architecture)
## CodeFarm_SplitDB
!["Supported Magento Version"][magento-badge] !["Latest Release"][release-badge]

Magento 2 CE Split Database to Master and Slave

* Compatible with _Magento Open Source_ `2.3.x` & `2.4.x`

## Features

This module provides the ability to route query from Frontend to specific MySql instance (write to master and select to slave).
The module currently provides the following advantage:
* Increasing performance of Frontend Store
* Prevent MySql server from deadlock (SQLSTATE[40001]: Serialization failure: 1213 Deadlock)

<details>
  <summary>Example Config</summary>
   
  ![Example Config](https://user-images.githubusercontent.com/4225347/112895353-ec7ccb00-90d4-11eb-937f-cd54636fbf19.png)
</details>

You need to change your enviroment config file - `env.php`.
### Old config:
![Screenshot from 2021-12-31 17-24-13](https://user-images.githubusercontent.com/96720166/147818073-4c2ac2ee-508d-4c40-bc54-efa6b246693e.png)

### New Config:
![Screenshot from 2021-12-31 17-25-16](https://user-images.githubusercontent.com/96720166/147818124-95c5d00c-ead1-4760-82d0-5425b7064e4a.png)


* To quick disable split database and route all query to master:
  `bin/magento db:mode:set default`
* To quick enable split database and route all query to master:
  `bin/magento db:mode:set split`

If you not config, default connection will be used.
  
  ## Requirements

* Magento Open Source version `2.3.x` or `2.4.x`
  
## Installation
Please install this module via Composer. This module is hosted on [Packagist][packagist].

* `composer require cdfrm/magento2-splitdb`
* `bin/magento module:enable CodeFarm_SplitDb`
* `bin/magento setup:upgrade`
## Testing
You can add log to check at: `\CodeFarm\SplitDb\Adapter\Pdo\Mysql::query`
![Screenshot from 2021-12-31 17-37-09](https://user-images.githubusercontent.com/96720166/147818754-465c288e-5d36-48b3-9683-1683aacf7ac0.png)

## Licence
[GPLv3][gpl] © [Pham Dai][author]

[magento-badge]:https://img.shields.io/badge/magento-2.3.x%20%7C%202.4.x-orange.svg?logo=magento&style=for-the-badge
[release-badge]:https://img.shields.io/github/v/release/robaimes/module-checkout-designs?sort=semver&style=for-the-badge&color=blue
[packagist]:https://packagist.org/packages/cdfrm/magento2-splitdb
[gpl]:https://www.gnu.org/licenses/gpl-3.0.en.html
[author]:https://www.linkedin.com/in/daipham3101/
