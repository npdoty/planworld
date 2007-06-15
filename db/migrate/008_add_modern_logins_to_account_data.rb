class AddModernLoginsToAccountData < ActiveRecord::Migration
  def self.up
    add_column :account_data, :crypted_password, :string, :length => 40
    add_column :account_data, :salt, :string, :length => 40
    add_column :account_data, :remember_token, :string
    add_column :account_data, :remember_token_expires_at, :datetime
  end

  def self.down
    remove_column :account_data, :crypted_password
    remove_column :account_data, :salt
    remove_column :account_data, :remember_token
    remove_column :account_data, :remember_token_expires_at
  end
end
