<?php
use Phinx\Seed\AbstractSeed;

class ProductSeeder extends AbstractSeed {
    
    public function run() {
        $data = [
            [
                'id' => 0,
                'uuid' => 'a34148b9-b7ec-43c4-981d-4d020c613556',
                'title' => 'Instructionset pdf',
                'desc' => 'something cool here',
                'slug' => 'instruction-set-pdf',
                'stripe_id' => 'prod_MRrc59UHEs2vqd',
                'file_path' => 'example/pp.pdf',
                'price' => 250,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => null,
                'user_id' => 0
            ]
        ];

        $users = $this->table('products');
        $users->insert($data)->saveData();
    }
}
