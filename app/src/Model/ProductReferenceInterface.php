<?php

namespace App\Model;

interface ProductReferenceInterface
{
    public const REFERENCE = [
        'BED_FEET' => self::BED_FEET,
        'PILLOW' => self::PILLOW,
        'SOFA_BED' => self::SOFA_BED,
        'BED_FRAME' => self::BED_FRAME,
        'BED_FRAME_COMBO' => self::BED_FRAME_COMBO,
        'PILLOW_COMBO' => self::PILLOW_COMBO,
        'BED_FEET_COMBO' => self::BED_FEET_COMBO,
        'STORAGE_BED_FRAME_COVER' => self::STORAGE_BED_FRAME_COVER,
        'SOFA_BED_DUO' => self::SOFA_BED_DUO,
        'STORAGE_BED_FRAME_COMBO' => self::STORAGE_BED_FRAME_COMBO,
    ];

    public const REPOSSESS_REFS = [
        self::REP_MAT,
        self::REP_SOM,
        self::REP_SOMC,
        self::REP_SOM_BOIS,
        self::REP_SOM_LAT,
        self::REP_TET,
        self::REP_SOF,
        self::REP_TPR,
        self::REP_CANAP,
    ];

    public const REF_MATTRESSES = [
        'mattress',
        'pelote-mattress',
        'hybrid-mattress',
    ];

    public const SOFA = [
        'sofa-bed',
        'sofa-bed-duo',
        'sofa-bed-solo',
    ];

    public const PELOTE = [
        'pack_pelote',
        'pelote-mattress',
    ];

    public const STORAGE_BED = [
        'storage-bed',
        'storage-bed-frame',
        'kid-storage-bed',
    ];

    public const ELECTRIC_BED_BEDFRAME = [
        'electric-bed',
        'electric-bed-frame',
    ];

    public const WOOD_BEDFRAME = [
        'bundle-combo-lit-bois',
        'wood-bed-frame',
        'wood-bed-kid',
    ];

    public const GO = [
        'go-bed',
        'go-bed-frame',
        'go-mattress',
        'go-pack',
    ];

    public const REFERENCE_BUILD = 'build';
    public const REFERENCE_RETURN_BUILD = 'return-build';
    public const REFERENCE_SHIPPING_PRODUCT = 'package-price';

    public const BED_FEET = 'TEDPB';
    public const PILLOW = 'TEDO';
    public const SOFA_BED = 'TEDCP';
    public const BED_FRAME = 'TEDSO';
    public const BED_FRAME_COMBO = 'TEDSOBIB';
    public const PILLOW_COMBO = 'TED2O';
    public const BED_FEET_COMBO = 'TED2P';
    public const SOFA_BED_DUO = 'TEDCPST';

    // Repossess codes
    public const REP_MAT = 'REPMAT';
    public const REP_SOM = 'REPSOM';
    public const REP_SOMC = 'REPSOMC';
    public const REP_SOM_BOIS = 'REPSOMBOIS';
    public const REP_SOM_LAT = 'REPSOMLAT';
    public const REP_TET = 'REPTET';
    public const REP_SOF = 'REPSOF';
    public const REP_TPR = 'REPTPR';
    public const REP_CANAP = 'REPCANAP';

    public const OLD_REP_MAT = 'REPMAT_OLD';
    public const OLD_REP_SOM = 'REPSOM_OLD';
    public const OLD_REP_SOMC = 'REPSOMC_OLD';
    public const OLD_REP_SOM_BOIS = 'REPSOMBOIS_OLD';
    public const OLD_REP_SOM_LAT = 'REPSOMLAT_OLD';
    public const OLD_REP_TET = 'REPTET_OLD';
    public const OLD_REP_SOF = 'REPSOF_OLD';
    public const OLD_REP_TPR = 'REPTPR_OLD';
    public const OLD_REP_CANAP = 'REPCANAP_OLD';

    public const OLD_REPOSSESS_CODES = [
        self::OLD_REP_MAT,
        self::OLD_REP_SOM,
        self::OLD_REP_SOMC,
        self::OLD_REP_SOM_BOIS,
        self::OLD_REP_SOM_LAT,
        self::OLD_REP_TET,
        self::OLD_REP_SOF,
        self::OLD_REP_TPR,
        self::OLD_REP_CANAP,
    ];

    // Assembly Codes
    public const MONT_CP = 'MONTCP';
    public const MONT_SC = 'MONTSC';
    public const INST_PEL = 'INSTPEL';
    public const MONT_TPR = 'MONTTPR';
    public const MONT_SBOIS = 'MONTSBOIS';
    public const MONT_GO = 'MONTGO';
    public const LIV_GO = 'LIVGO';

    // Assembly Product Codes
    public const TED_CP = 'TEDCP';
    public const TED_SC_CM = 'TEDSCCM';
    public const TED_PEL = 'TEDPEL';
    public const TED_TPR_CM = 'TEDTPRCM';
    public const TED_SBOIS_CM = 'TEDSBOISCM';
    public const TED_GO = 'GO';

    public const INST_REFS = [
        self::INST_PEL,
        self::LIV_GO,
    ];

    public const ASSEMBLY_CODES = [
        self::MONT_CP,
        self::MONT_SC,
        self::INST_PEL,
        self::MONT_TPR,
        self::MONT_SBOIS,
        self::MONT_GO,
    ];

    public const ASSEMBLY_MATCHES = [
        self::MONT_CP => self::TED_CP,
        self::MONT_SC => self::TED_SC_CM,
        self::INST_PEL => self::TED_PEL,
        self::MONT_TPR => self::TED_TPR_CM,
        self::MONT_SBOIS => self::TED_SBOIS_CM,
        self::MONT_GO => self::TED_GO,
    ];

    public const STORAGE_BED_FRAME_COVER = 'TEDSCH';
    public const STORAGE_BED_FRAME_COMBO = 'TEDSCCM';

    public const SOFA_BED_DUO_PRODUCT_CODE = 'sofa-bed-duo';
    public const SOFA_SIMPLE_PRODUCT_CODE = 'sofa-simple';
    /* Bed-Frame Type */
    public const STORAGE_BED_FRAME_PRODUCT_CODE = 'storage-bed-frame';
    public const KID_STORAGE_BED_FRAME_PRODUCT_CODE = 'kid-storage-bed-frame';
    public const ELECTRIC_BED_FRAME_PRODUCT_CODE = 'electric-bed-frame';
    public const BED_FRAME_PRODUCT_CODE = 'bed-frame';
    public const WOOD_BED_FRAME_PRODUCT_CODE = 'wood-bed-frame';
    public const STEEL_BED_FRAME_PRODUCT_CODE = 'steel-bed-frame';

    /* REP bed-frame */
    public const REF_REP_BED_FRAME = [
        self::REP_SOM => self::BED_FRAME_PRODUCT_CODE,
        self::REP_SOM_BOIS => self::WOOD_BED_FRAME_PRODUCT_CODE,
        self::REP_SOM_LAT => self::STEEL_BED_FRAME_PRODUCT_CODE,
        self::REP_SOMC => self::STORAGE_BED_FRAME_PRODUCT_CODE,
        self::REP_TPR => self::ELECTRIC_BED_FRAME_PRODUCT_CODE,
    ];

    public const BUNDLE_BED_FRAME_TYPE = [
        'bundle-bed-frame-type_1' => 'bed-frame',
        'bundle-bed-frame-type_2' => 'storage-bed-frame',
        'bundle-bed-frame-type_3' => 'steel-bed-frame',
        'bundle-bed-frame-type_4' => 'wood-bed-frame',
    ];
    public const BUNDLE_ESSENTIEL = 'bundle-essentiel';
    public const BUNDLE_CONFORT = 'bundle-confort';

    // Todo: delete to find a better solution
    public const BUNDLE_PRICE_MATCH = [
        self::BUNDLE_ESSENTIEL => [
            'bundle-bed-frame-type_3' => [
                80 => 0,
                90 => 0,
                120 => 0,
                140 => 0,
                160 => 0,
            ],
            'bundle-bed-frame-type_1' => [
                80 => 40,
                90 => 45,
                120 => 50,
                140 => 80,
                160 => 160,
                180 => 0,
                200 => 0,
            ],
            'bundle-bed-frame-type_2' => [
                90 => 219,
                140 => 329,
                160 => 329,
                180 => 199,
            ],
        ],
        self::BUNDLE_CONFORT => [
            'bundle-bed-frame-type_1' => [
                80 => 0,
                90 => 0,
                120 => 0,
                140 => 0,
                160 => 0,
                180 => 0,
                200 => 0,
            ],
            'bundle-bed-frame-type_2' => [
                90 => 174,
                140 => 249,
                160 => 169,
                180 => 199,
            ],
            'bundle-bed-frame-type_4' => [
                120 => 450,
                140 => 400,
                160 => 400,
                180 => 400,
            ],
        ],
    ];
}
