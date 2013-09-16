OpenMiamMiam [![Build Status](https://secure.travis-ci.org/twbs/bootstrap.png)](https://travis-ci.org/isics/OpenMiamMiam)
============

OpenMiamMiam is an open source pre-order platform designed for collectives points of sale.


## Requirements

- PHP 5.4

## Configuration reference

All available configuration options are listed below with their default values.

    # app/config/config.yml
    isics_open_miam_miam:
        currency:                EUR               # Currency (ISO code)
        title:                   OpenMiamMiam Demo # Title
        product:
            ref_prefix:          PR                # Product ref prefix
            ref_pad_length:      3                 # Product ref pad length (example: "PR001")
        customer:
            ref_prefix:          CU                # Customer ref prefix
            ref_pad_length:      6                 # Customer ref pad length (example: "CU000001")
        order:
            ref_prefix:          OR                # Order ref prefix
            ref_pad_length:      6                 # Order ref pad length (example: "OR000001")
        buying_units:            [piece, g, kg, m] # Buying units

## License

OpenMiamMiamBundle is subject to the GNU AFFERO GENERAL PUBLIC LICENSE v3
that is bundled with this source code in the file LICENSE.

## Credits

OpenMiamMiamBundle is developped by Isics (www.isics.fr).
