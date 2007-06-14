require 'date'
require 'time'

class << ActiveRecord::Base
  def has_association?(association)
    reflections.keys.include? association
  end
end

class Time
  def in_week?(week)
    (week.is_a?(Range) ? week : (week.monday..week.next_week)) === self
  end
end


class Time
  def to_s(format = :default)
    case DATE_FORMATS[format] 
    when Proc   then DATE_FORMATS[format].call(self)
    when String then strftime(DATE_FORMATS[format]).strip
    else to_default_s
    end
  end

  def week
    monday..next_week
  end

  def self.this_week
    Time.now.week
  end
end

class Module
  def metaclass_accessor(attribute, default = nil)
    module_eval <<-EVAL
      def #{attribute}(value = nil)
        if value
          write_inheritable_attribute #{attribute.inspect}, value
        else
          read_inheritable_attribute(#{attribute.inspect}) || #{default.inspect}
        end
      end
      alias_method '#{attribute}=', #{attribute.inspect}
    EVAL
  end
  alias_method :metaclass_accessor_with_default, :metaclass_accessor
end

module ActionView::Helpers::UrlHelper
  %w(link_to link_to_unless_current).each do |method|
    define_method("#{method}_with_auto_detection") do |*args|
      __send__("#{method}_without_auto_detection", *(infer_record_text_and_path(args.shift) + args))
    end
    alias_method_chain method, :auto_detection
  end

protected
  def infer_record_text_and_path(record)
    return Array(record) unless record.is_a? ActiveRecord::Base
    [ infer_record_text(record), infer_record_link(record) ]
  end

  def infer_record_text(record)
    return record.to_s if record.class.base_class.public_instance_methods(false).include? "to_s"
    title = self.class.name.titleize
    record.new_record? ? "New #{title}" : "#{title} ##{id}"
  end

  def infer_record_link(record)
    __send__("#{record.class.base_class.to_s.underscore}_path", record)
  end
end

class ActionView::Base
  def render_with_auto_detection(options = {}, old_local_assigns = {}, &block) 
    if options.is_a?(Array) || options.is_a?(ActiveRecord::Base)
      items = options
    end

    returning '' do |output|
      Array(items).each do |item|
        render_options = { 
          :partial => "#{item.class.base_class.to_s.tableize}/#{item.class.base_class.to_s.underscore}" ,
          :locals  => { item.class.base_class.to_s.underscore.to_sym => item }
        }
        render_options[:locals].update(old_local_assigns[:locals]) if old_local_assigns.has_key? :locals

        output << render_without_auto_detection(render_options, old_local_assigns, &block)
      end

      output << render_without_auto_detection(options, old_local_assigns, &block).to_s if output.blank?
    end
  end
  alias_method_chain :render, :auto_detection

  def dom_id(record)
    "#{record.class.base_class.to_s.underscore}_#{record.id}"
  end
  
  def dom_class(record)
    "#{record.class.base_class.to_s.underscore}"
  end
end

##
# http://blog.codahale.com/2006/04/09/rails-environments-a-plugin-for-well-rails/
module Rails
  def self.environment
    ENV['RAILS_ENV'].to_s.downcase
  end

  def self.development?
    environment == 'development'
  end

  def self.production?
    environment == 'production'
  end

  def self.staging?
    environment == 'staging'
  end

  def self.test?
    environment == 'test'
  end
  
  def self.none?
    environment.empty?
  end
end
