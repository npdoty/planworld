class Float
  # >> 2.12235235235.round(4)
  # => 2.1224
  # >> 2.12235235235.round(1)
  # => 2.1
  # >> 2.12235235235.round(1)
  # => 2.0
  def round(places = 0) 
    ("%.0#{places}f" % self).to_f
  end
end

class Module
  # class Speakers
  #   def self.volume
  #     @@volume
  #   end
  #   alias_class_method :loudness, :volume
  # end
  #
  def alias_class_method(target, method)
    meta_eval do
      alias_method target, method
    end
  end
end

class Object
  def returning(value)
    yield(value)
    value
  end unless respond_to? :returning

  # Metaid == a few simple metaclass helper
  # (See http://whytheluckystiff.net/articles/seeingMetaclassesClearly.html.)
  # The hidden singleton lurks behind everyone
  def metaclass() class << self; self end end
  def meta_eval(&blk) metaclass.instance_eval(&blk) end

  # Adds methods to a metaclass
  def meta_def(name, &blk)
    meta_eval { define_method(name, &blk) }
  end

  # Defines an instance method within a class
  def class_def(name, &blk)
    class_eval { define_method(name, &blk) }
  end
end

class Symbol
  def to_proc
    Proc.new { |*args| args.shift.__send__(self, *args) }
  end unless respond_to? :to_proc
end

class Hash
  # >> { :dog => true, :cat => true, :human => true }.pass(:dog, :human)
  # => { :dog => true, :human => true }
  def pass(*keys) 
    select { |k,v| keys.include? k }.to_hash
  end

  # >> { :dog => true, :cat => true, :human => true }.no_pass(:dog, :human)
  # => { :cat => true }
  def no_pass(*keys) 
    reject { |k,v| keys.include? k } 
  end
  
  # >> { :dog => true, :cat => true, :human => true }.delete_keys(:dog, :human)
  # => { :cat => true }
  def delete_keys(*keys)
    delete_if { |key, value| keys.include? key }
  end

  def self.new_with_arrays
    # >> hash = Hash.new_with_arrays
    # => {}
    # >> hash[:dog] << 'tanner'
    # => ["tanner"]
    Hash.new_with(Array)
  end

  def self.new_with_hashes
    # >> hash = { :dog => { :canine => true }, :cat => { :feline => true } }
    # >> hash[:duck]
    # => {}
    # >> hash[:duck][:donald] = true
    # => true
    Hash.new_with(Hash)
  end

  def self.new_with(type)
    # >> hash = Hash.new_with(String)
    # => {}
    # >> hash[:dog]
    # => ""
    Hash.new { |hash, key| hash[key] = type.new }
  end
end

class String
  # >> 'chow:hound' / ':'
  # => ["chow", "hound"]
  def /(delimiter)
    split(delimiter)
  end

  def trim
    strip.squeeze(" ")
  end

  # m = "12:34:56".match(/(\d+):(\d+):(\d+)/, :hour, :minute, :second)
  # puts m.hour
  # puts m.second
  alias :real_match :match
  def match(*args)
    return unless match = real_match(args.first) 
    returning match do |m|
      args[1..-1].each_with_index { |name, index| m.meta_def(name) { self[index+1] } }
    end
  end
end

class Array
  # >> [:dog, true, :cat, true].to_hash
  # => { :dog => true, :cat => true }
  def to_hash
    Hash[*inject([]) { |array, (key, value)| array + [key, value] }]
  end
  alias :to_h :to_hash

  # >> [1,2,3,4,5,6,7,8,9,10] / 4
  # => [ [1,2,3], [4,5,6], [7,8,9], [10] ]
  def divide_by(num_divisions)
    return [] if empty?
    require 'enumerator'
    max_items_per_division = (size + (num_divisions - 1)) / num_divisions
    returning([]) do |columns|
      each_slice(max_items_per_division) { |n| columns << n }
    end
  end
  alias :/ :divide_by

  def subtract_by_value(r)
    # set subtraction by value (works with hashs within arrays)
    reject { |item| r.include? item }.uniq
  end
end

module Kernel
  def calling_method
    caller[2].to_s[/`([^']+)'$/].intern rescue nil
  end

private
  def this_method
    caller[0] =~ /`([^']*)'/ and $1
  end
end
