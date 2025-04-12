import 'user.dart';
import 'property.dart';

class Agent {
  final int id;
  final int userId;
  final String businessName;
  final String? businessAddress;
  final String? businessPhone;
  final String? businessEmail;
  final String? businessLicense;
  final String subscriptionType;
  final DateTime subscriptionExpiry;
  final List<Property>? properties;
  final User? user;
  final DateTime createdAt;
  final DateTime updatedAt;

  Agent({
    required this.id,
    required this.userId,
    required this.businessName,
    this.businessAddress,
    this.businessPhone,
    this.businessEmail,
    this.businessLicense,
    required this.subscriptionType,
    required this.subscriptionExpiry,
    this.properties,
    this.user,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Agent.fromJson(Map<String, dynamic> json) {
    return Agent(
      id: json['id'],
      userId: json['user_id'],
      businessName: json['business_name'],
      businessAddress: json['business_address'],
      businessPhone: json['business_phone'],
      businessEmail: json['business_email'],
      businessLicense: json['business_license'],
      subscriptionType: json['subscription_type'],
      subscriptionExpiry: DateTime.parse(json['subscription_expiry']),
      properties: json['properties'] != null
          ? List<Property>.from(
              json['properties'].map((x) => Property.fromJson(x)))
          : null,
      user: json['user'] != null ? User.fromJson(json['user']) : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_id': userId,
      'business_name': businessName,
      'business_address': businessAddress,
      'business_phone': businessPhone,
      'business_email': businessEmail,
      'business_license': businessLicense,
      'subscription_type': subscriptionType,
      'subscription_expiry': subscriptionExpiry.toIso8601String(),
      'properties': properties?.map((x) => x.toJson()).toList(),
      'user': user?.toJson(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  Agent copyWith({
    int? id,
    int? userId,
    String? businessName,
    String? businessAddress,
    String? businessPhone,
    String? businessEmail,
    String? businessLicense,
    String? subscriptionType,
    DateTime? subscriptionExpiry,
    List<Property>? properties,
    User? user,
    DateTime? createdAt,
    DateTime? updatedAt,
  }) {
    return Agent(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      businessName: businessName ?? this.businessName,
      businessAddress: businessAddress ?? this.businessAddress,
      businessPhone: businessPhone ?? this.businessPhone,
      businessEmail: businessEmail ?? this.businessEmail,
      businessLicense: businessLicense ?? this.businessLicense,
      subscriptionType: subscriptionType ?? this.subscriptionType,
      subscriptionExpiry: subscriptionExpiry ?? this.subscriptionExpiry,
      properties: properties ?? this.properties,
      user: user ?? this.user,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
    );
  }

  bool get isSubscriptionActive {
    return DateTime.now().isBefore(subscriptionExpiry);
  }

  bool get canAddProperty {
    if (!isSubscriptionActive) return false;
    
    final propertyCount = properties?.length ?? 0;
    switch (subscriptionType.toLowerCase()) {
      case 'base':
        return propertyCount < 4;
      case 'gold':
        return propertyCount < 10;
      case 'vip':
        return true;
      default:
        return false;
    }
  }

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
  
    return other is Agent &&
      other.id == id &&
      other.userId == userId &&
      other.businessName == businessName &&
      other.businessAddress == businessAddress &&
      other.businessPhone == businessPhone &&
      other.businessEmail == businessEmail &&
      other.businessLicense == businessLicense &&
      other.subscriptionType == subscriptionType &&
      other.subscriptionExpiry == subscriptionExpiry;
  }

  @override
  int get hashCode {
    return id.hashCode ^
      userId.hashCode ^
      businessName.hashCode ^
      businessAddress.hashCode ^
      businessPhone.hashCode ^
      businessEmail.hashCode ^
      businessLicense.hashCode ^
      subscriptionType.hashCode ^
      subscriptionExpiry.hashCode;
  }
}
