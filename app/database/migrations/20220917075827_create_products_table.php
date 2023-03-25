<?php
declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Migration\AbstractMigration;

final class CreateProductsTable extends AbstractMigration {
    
    private $schema;

    public function init() {
        $this->schema = (new Capsule)->schema();
    }

    public function up() {
        $this->schema->create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('desc')->nullable();
            $table->string('stripe_id');
            $table->string('file_path');
            $table->integer('price');
            $table->timestamps();
            $table->foreignIdFor(User::class);
        });
    }

    public function down() {
       $this->schema->drop('products');
    }

}
