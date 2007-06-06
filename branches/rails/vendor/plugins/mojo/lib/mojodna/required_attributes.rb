##
# Adds a Model.required_attributes method which returns an array of attributes
# that are required by validates_presence_of
module MojoDNA
  module RequiredAttributes

    def self.included(base)
      # TODO this could use some cleanup, as it doesn't quite seem like the
      # right way to hook in
      base.extend ClassMethods
      ActiveRecord::Validations::ClassMethods.module_eval do
        include MojoDNA::RequiredAttributes::ClassMethods

        alias_method_chain :validates_presence_of, :required_attributes
      end
    end
    
    module ClassMethods
      def requires?(attr_name)
        required_attributes.include?(attr_name)
      end
      
      def required_attributes(on = :save)
        @@required_attributes[on]
      end
    
      def validates_presence_of_with_required_attributes(*attr_names)
        on = :save
        on = attr_names.last[:on] if attr_names.last.is_a?(Hash) and attr_names.last[:on]

        @@required_attributes ||= {}
        @@required_attributes[on] ||= []
        attr_names.each do |attr_name|
          @@required_attributes[on] << attr_name unless attr_name.is_a?(Hash)
        end
      
        validates_presence_of_without_required_attributes(*attr_names)
      end
    end

  end
end

ActiveRecord::Base.send :include, MojoDNA::RequiredAttributes
