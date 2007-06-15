class UpdatePlanwatchGroups < ActiveRecord::Migration
  def self.up
    create_table :planwatch_groups do |t|
      t.integer  :user_id
      t.string   :name
      t.integer  :position
      
      t.datetime :updated_at
      t.datetime :created_at
    end
    
    execute "INSERT INTO planwatch_groups SELECT gid, uid, name, pos, NOW(), NOW() FROM pw_groups"
    
    drop_table :pw_groups
  end

  def self.down
    raise ActiveRecord::IrreversibleMigration.new("Legacy planwatch groups table cannot be recreated.")
  end
end
