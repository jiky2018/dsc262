<?php

$api_data = array(
    array(
        'name' => '用户',
        'cat' => 'user',
        'list' => array(
            array(
                'name' => '获取会员列表',
                'val' => 'dsc.user.list.get'
            ),
            array(
                'name' => '获取单条会员信息',
                'val' => 'dsc.user.info.get'
            ),
            array(
                'name' => '插入会员信息',
                'val' => 'dsc.user.insert.post'
            ),
            array(
                'name' => '更新会员信息',
                'val' => 'dsc.user.update.post'
            ),
            array(
                'name' => '删除会员信息',
                'val' => 'dsc.user.del.get'
            ),
            array(
                'name' => '获取会员等级列表',
                'val' => 'dsc.user.rank.list.get'
            ),
            array(
                'name' => '获取单条会员等级信息',
                'val' => 'dsc.user.rank.info.get'
            ),
            array(
                'name' => '插入会员等级信息',
                'val' => 'dsc.user.rank.insert.post'
            ),
            array(
                'name' => '更新会员等级信息',
                'val' => 'dsc.user.rank.update.post'
            ),
            array(
                'name' => '删除会员等级信息',
                'val' => 'dsc.user.rank.del.get'
            )
        )
    ),
    array(
        'name' => '类目',
        'cat' => 'category',
        'list' => array(
            array(
                'name' => '获取分类列表',
                'val' => 'dsc.category.list.get'
            ),
            array(
                'name' => '获取单条分类信息',
                'val' => 'dsc.category.info.get'
            ),
            array(
                'name' => '插入分类信息',
                'val' => 'dsc.category.insert.post'
            ),
            array(
                'name' => '更新分类信息',
                'val' => 'dsc.category.update.post'
            ),
            array(
                'name' => '删除分类信息',
                'val' => 'dsc.category.del.get'
            ),
            array(
                'name' => '获取商家分类列表',
                'val' => 'dsc.category.seller.list.get'
            ),
            array(
                'name' => '获取单条商家分类信息',
                'val' => 'dsc.category.seller.info.get'
            ),
            array(
                'name' => '插入商家分类信息',
                'val' => 'dsc.category.seller.insert.post'
            ),
            array(
                'name' => '更新商家分类信息',
                'val' => 'dsc.category.seller.update.post'
            ),
            array(
                'name' => '删除商家分类信息',
                'val' => 'dsc.category.seller.del.get'
            )
        )
    ), array(
        'name' => '商品',
        'cat' => 'goods',
        'list' => array(
            array(
                'name' => '获取商品列表',
                'val' => 'dsc.goods.list.get'
            ),
            array(
                'name' => '获取单条商品信息',
                'val' => 'dsc.goods.info.get'
            ),
            array(
                'name' => '插入商品信息',
                'val' => 'dsc.goods.insert.post'
            ),
            array(
                'name' => '更新商品信息',
                'val' => 'dsc.goods.update.post'
            ),
            array(
                'name' => '删除商品信息',
                'val' => 'dsc.goods.del.get'
            ),
            array(
                'name' => '获取商品仓库列表',
                'val' => 'dsc.goods.warehouse.list.get'
            ),
            array(
                'name' => '获取单条商品仓库信息',
                'val' => 'dsc.goods.warehouse.info.get'
            ),
            array(
                'name' => '插入商品仓库信息',
                'val' => 'dsc.goods.warehouse.insert.post'
            ),
            array(
                'name' => '更新商品仓库信息',
                'val' => 'dsc.goods.warehouse.update.post'
            ),
            array(
                'name' => '删除商品仓库信息',
                'val' => 'dsc.goods.warehouse.del.get'
            ),
            array(
                'name' => '获取商品地区列表',
                'val' => 'dsc.goods.area.list.get'
            ),
            array(
                'name' => '获取单条商品地区信息',
                'val' => 'dsc.goods.area.info.get'
            ),
            array(
                'name' => '插入商品地区信息',
                'val' => 'dsc.goods.area.insert.post'
            ),
            array(
                'name' => '更新商品地区信息',
                'val' => 'dsc.goods.area.update.post'
            ),
            array(
                'name' => '删除商品地区信息',
                'val' => 'dsc.goods.area.del.get'
            ),
            array(
                'name' => '获取商品相册列表',
                'val' => 'dsc.goods.gallery.list.get'
            ),
            array(
                'name' => '获取单条商品相册信息',
                'val' => 'dsc.goods.gallery.info.get'
            ),
            array(
                'name' => '插入商品相册信息',
                'val' => 'dsc.goods.gallery.insert.post'
            ),
            array(
                'name' => '更新商品相册信息',
                'val' => 'dsc.goods.gallery.update.post'
            ),
            array(
                'name' => '删除商品相册信息',
                'val' => 'dsc.goods.gallery.del.get'
            ),
            array(
                'name' => '获取商品属性列表',
                'val' => 'dsc.goods.attr.list.get'
            ),
            array(
                'name' => '获取单条商品属性信息',
                'val' => 'dsc.goods.attr.info.get'
            ),
            array(
                'name' => '插入商品属性信息',
                'val' => 'dsc.goods.attr.insert.post'
            ),
            array(
                'name' => '更新商品属性信息',
                'val' => 'dsc.goods.attr.update.post'
            ),
            array(
                'name' => '删除商品属性信息',
                'val' => 'dsc.goods.attr.del.get'
            ),
            array(
                'name' => '获取商品运费模板列表',
                'val' => 'dsc.goods.freight.list.get'
            ),
            array(
                'name' => '获取单条商品运费模板信息',
                'val' => 'dsc.goods.freight.info.get'
            ),
            array(
                'name' => '插入商品运费模板信息',
                'val' => 'dsc.goods.freight.insert.post'
            ),
            array(
                'name' => '更新商品运费模板信息',
                'val' => 'dsc.goods.freight.update.post'
            ),
            array(
                'name' => '删除商品运费模板信息',
                'val' => 'dsc.goods.freight.del.get'
            ),
            array(
                'name' => '批量插入商品信息',
                'val' => 'dsc.goods.batchinsert.post'
            ),
            array(
                'name' => '信息更新通知',
                'val' => 'dsc.goods.notification.update.post'
            )
        )
    ),
    array(
        'name' => '商品货品',
        'cat' => 'product',
        'list' => array(
            array(
                'name' => '获取商品货品列表',
                'val' => 'dsc.product.list.get'
            ),
            array(
                'name' => '获取单条商品货品信息',
                'val' => 'dsc.product.info.get'
            ),
            array(
                'name' => '插入商品货品信息',
                'val' => 'dsc.product.insert.post'
            ),
            array(
                'name' => '更新商品货品信息',
                'val' => 'dsc.product.update.post'
            ),
            array(
                'name' => '删除商品货品信息',
                'val' => 'dsc.product.del.get'
            ),
            array(
                'name' => '获取商品仓库货品列表',
                'val' => 'dsc.product.warehouse.list.get'
            ),
            array(
                'name' => '获取单条商品仓库货品信息',
                'val' => 'dsc.product.warehouse.info.get'
            ),
            array(
                'name' => '插入商品仓库货品信息',
                'val' => 'dsc.product.warehouse.insert.post'
            ),
            array(
                'name' => '更新商品仓库货品信息',
                'val' => 'dsc.product.warehouse.update.post'
            ),
            array(
                'name' => '删除商品仓库货品信息',
                'val' => 'dsc.product.warehouse.del.get'
            ),
            array(
                'name' => '获取商品地区货品列表',
                'val' => 'dsc.product.area.list.get'
            ),
            array(
                'name' => '获取单条商品地区货品信息',
                'val' => 'dsc.product.area.info.get'
            ),
            array(
                'name' => '插入商品地区货品信息',
                'val' => 'dsc.product.area.insert.post'
            ),
            array(
                'name' => '更新商品地区货品信息',
                'val' => 'dsc.product.area.update.post'
            ),
            array(
                'name' => '删除商品地区货品信息',
                'val' => 'dsc.product.area.del.get'
            )
        )
    ),
    array(
        'name' => '品牌',
        'cat' => 'brand',
        'list' => array(
            array(
                'name' => '获取品牌列表',
                'val' => 'dsc.brand.list.get'
            ),
            array(
                'name' => '获取单条品牌信息',
                'val' => 'dsc.brand.info.get'
            ),
            array(
                'name' => '插入品牌信息',
                'val' => 'dsc.brand.insert.post'
            ),
            array(
                'name' => '更新品牌信息',
                'val' => 'dsc.brand.update.post'
            ),
            array(
                'name' => '删除品牌信息',
                'val' => 'dsc.brand.del.get'
            )
        )
    ),
    array(
        'name' => '交易',
        'cat' => 'order',
        'list' => array(
            array(
                'name' => '获取订单列表',
                'val' => 'dsc.order.list.get'
            ),
            array(
                'name' => '获取单条订单信息',
                'val' => 'dsc.order.info.get'
            ),
            array(
                'name' => '插入订单信息',
                'val' => 'dsc.order.insert.post'
            ),
            array(
                'name' => '更新订单信息',
                'val' => 'dsc.order.update.post'
            ),
            array(
                'name' => '删除订单信息',
                'val' => 'dsc.order.del.get'
            ),
            array(
                'name' => '获取订单商品列表',
                'val' => 'dsc.order.goods.list.get'
            ),
            array(
                'name' => '获取单条订单商品信息',
                'val' => 'dsc.order.goods.info.get'
            ),
            array(
                'name' => '插入订单商品信息',
                'val' => 'dsc.order.goods.insert.post'
            ),
            array(
                'name' => '更新订单商品信息',
                'val' => 'dsc.order.goods.update.post'
            ),
            array(
                'name' => '删除订单商品信息',
                'val' => 'dsc.order.goods.del.get'
            ),
            array(
                'name' => '订单发货',
                'val' => 'dsc.order.confirmorder.post'
            )
        )
    ),
    array(
        'name' => '属性类型',
        'cat' => 'goodstype',
        'list' => array(
            array(
                'name' => '获取属性类型列表',
                'val' => 'dsc.goodstype.list.get'
            ),
            array(
                'name' => '获取单条属性类型信息',
                'val' => 'dsc.goodstype.info.get'
            ),
            array(
                'name' => '插入属性类型信息',
                'val' => 'dsc.goodstype.insert.post'
            ),
            array(
                'name' => '更新属性类型信息',
                'val' => 'dsc.goodstype.update.post'
            ),
            array(
                'name' => '删除属性类型信息',
                'val' => 'dsc.goodstype.del.get'
            ),
            array(
                'name' => '获取属性列表',
                'val' => 'dsc.attribute.list.get'
            ),
            array(
                'name' => '获取单条属性信息',
                'val' => 'dsc.attribute.info.get'
            ),
            array(
                'name' => '插入属性信息',
                'val' => 'dsc.attribute.insert.post'
            ),
            array(
                'name' => '更新属性信息',
                'val' => 'dsc.attribute.update.post'
            ),
            array(
                'name' => '删除属性信息',
                'val' => 'dsc.attribute.del.get'
            )
        )
    ),
    array(
        'name' => '地区',
        'cat' => 'region',
        'list' => array(
            array(
                'name' => '获取地区列表',
                'val' => 'dsc.region.list.get'
            ),
            array(
                'name' => '获取单条地区信息',
                'val' => 'dsc.region.info.get'
            ),
            array(
                'name' => '插入地区信息',
                'val' => 'dsc.region.insert.post'
            ),
            array(
                'name' => '更新地区信息',
                'val' => 'dsc.region.update.post'
            ),
            array(
                'name' => '删除地区信息',
                'val' => 'dsc.region.del.get'
            )
        )
    ),
    array(
        'name' => '仓库地区',
        'cat' => 'warehouse',
        'list' => array(
            array(
                'name' => '获取仓库地区列表',
                'val' => 'dsc.warehouse.list.get'
            ),
            array(
                'name' => '获取单条仓库地区信息',
                'val' => 'dsc.warehouse.info.get'
            ),
            array(
                'name' => '插入仓库地区信息',
                'val' => 'dsc.warehouse.insert.post'
            ),
            array(
                'name' => '更新仓库地区信息',
                'val' => 'dsc.warehouse.update.post'
            ),
            array(
                'name' => '删除仓库地区信息',
                'val' => 'dsc.warehouse.del.get'
            )
        )
    ),
);
?>