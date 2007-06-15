class UpdatePlanwatch < ActiveRecord::Migration
  def self.up
    create_table :planwatch_entries do |t|
      t.integer  :user_id
      t.integer  :watched_user_id
      t.integer  :planwatch_group_id
      t.datetime :last_viewed_at
      t.datetime :updated_at
      t.datetime :created_at
    end
    
    execute "INSERT INTO planwatch_entries SELECT 0, uid, w_uid, gid, FROM_UNIXTIME(last_view), FROM_UNIXTIME(last_view), FROM_UNIXTIME(last_view) FROM planwatch"
    
    drop_table :planwatch
  end

  def self.down
    raise ActiveRecord::IrreversibleMigration.new("Legacy planwatch table cannot be recreated.")
  end
end
