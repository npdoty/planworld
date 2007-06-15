class AccountData < ActiveRecord::Base
  set_table_name "account_data"
  
  belongs_to :user
  
  # Virtual attribute for the unencrypted password
  attr_accessor :password
  before_save :encrypt_password
  
  # Encrypts some data with the salt.
  def self.encrypt(password, salt)
    Digest::SHA1.hexdigest("--#{salt}--#{password}--")
  end
  
  # Encrypts the password with the user salt
  def encrypt(password)
    self.class.encrypt(password, self.salt)
  end
  
protected

  # before filter 
  def encrypt_password
    return if password.blank?
    self.salt = Digest::SHA1.hexdigest("--#{Time.now.to_s}--#{username}--") if new_record?
    self.crypted_password = encrypt(password)
  end
end
