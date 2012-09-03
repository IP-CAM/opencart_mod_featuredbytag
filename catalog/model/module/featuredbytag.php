<?php
class ModelModuleFeaturedbytag extends Model {
    
    private $module = "featuredbytag";
    
    public function getFeaturedByTagProducts($limit, $tag = null)
    {
        $this->load->model('catalog/product');
        
        $limit = (int)$limit;
        
        $language_id       = (int)$this->config->get('config_language_id');
        $store_id          = (int)$this->config->get('config_store_id');
        $customer_group_id = $this->config->get('config_customer_group_id');
        
        if($this->customer->isLogged()) {
            $customer_group_id = $this->customer->getCustomerGroupId();
        }
        
        $cache_data = $this->cache->get('product.'.$this->module.'.'.$language_id.'.'.$store_id.'.'.$customer_group_id.'.'.$tag.'.'.$limit);
        
        if(!$cache_data) {
            $cache_data = array();
            
            $sql = "SELECT p.`product_id`
                FROM `".DB_PREFIX."product` p
                    LEFT JOIN `".DB_PREFIX."product_to_store` p2s ON (
                        p.`product_id` = p2s.`product_id`
                        AND p2s.`store_id` = '".(int)$this->config->get('config_store_id')."'
                    )
                    LEFT JOIN `".DB_PREFIX."product_tag` pt ON p.`product_id` = pt.`product_id`
                WHERE p.`status` = '1'
                AND p.`date_available` <= '".date('Y-m-d')."'
                AND pt.`tag` = '".$tag."'
                GROUP BY p.`product_id`
                ORDER BY p.`viewed` DESC
                LIMIT ".(int)$limit;
            
            $query = $this->db->query($sql);
            
            foreach($query->rows as $product) {
                $cache_data[$product["product_id"]] = $this->model_catalog_product->getProduct($product["product_id"]);
            }
            
            $this->cache->set('product.'.$this->module.'.'.$language_id.'.'.$store_id.'.'.$customer_group_id.'.'.$tag.'.'.$limit, $cache_data);
        }
        
        return $cache_data;
    }
    
}