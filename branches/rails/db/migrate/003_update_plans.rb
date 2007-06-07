class UpdatePlans < ActiveRecord::Migration
  class Plan < ActiveRecord::Base; end
  
  def self.up
    rename_column :plans, :uid, :user_id
    rename_column :plans, :content, :body
    add_column    :plans, :created_at, :datetime
    add_column    :plans, :updated_at, :datetime
  end

  def self.down
    rename_column :plans, :user_id, :uid
    rename_column :plans, :body, :content
    remove_column :plans, :created_at
    remove_column :plans, :updated_at
  end
end
