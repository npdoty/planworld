class FixAccountData < ActiveRecord::Migration
  def self.up
    rename_table :AccountData, :account_data
    add_column   :account_data, :new_sex, :string, :limit => 1
    add_column   :account_data, :new_confirmed, :boolean
    
    
    execute "UPDATE account_data SET new_sex=sex, new_confirmed=if(confirmed = 'Y', 1, 0)"
    
    remove_column :account_data, :sex
    remove_column :account_data, :confirmed
    
    rename_column :account_data, :new_sex, :sex
    rename_column :account_data, :new_confirmed, :confirmed
  end

  def self.down
  end
end
